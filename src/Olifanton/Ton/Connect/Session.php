<?php declare(strict_types=1);

namespace Olifanton\Ton\Connect;

use Http\Client\Common\HttpMethodsClientInterface;
use Olifanton\Ton\Connect\Events\Connect;
use Olifanton\Ton\Connect\Events\NullEvent;
use Olifanton\Ton\Connect\Exceptions\SessionException;
use Olifanton\Ton\Connect\Models\WalletApplication;
use Olifanton\Ton\Connect\Request\ConnectRequest;
use Olifanton\Ton\Connect\Request\SendTransactionRequest;
use Olifanton\Ton\Connect\Sse\Client;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class Session implements LoggerAwareInterface, \JsonSerializable
{
    use LoggerAwareTrait;

    private ?string $clientId = null;

    private int $lastEventId = 0;

    private int $lastRequestId = 0;

    private ?string $preconnectedId = null;

    public function __construct(
        private readonly string $id,
        private readonly string $secretKey,
        private readonly string $bridgeUrl,
    ) {}

    /**
     * @throws SessionException
     */
    public static function restore(string|array $sessionData): self
    {
        try {
            if (is_string($sessionData)) {
                $data = json_decode($sessionData, true, 8, JSON_THROW_ON_ERROR);
            } else {
                $data = $sessionData;
            }

            $instance = new self(
                sodium_hex2bin($data["id"]),
                sodium_hex2bin($data["secret_key"]),
                $data["bridge_url"],
            );
            $instance->clientId = sodium_hex2bin($data["client_id"]);
            $instance->lastEventId = $data["last_event_id"];
            $instance->lastRequestId = $data["last_request_id"] ?? 0;
            $instance->preconnectedId = $data["preconnected_id"] ?? null;

            return $instance;
        } catch (\Throwable $e) {
            throw new SessionException(
                "Session restoring error: " . $e->getMessage(),
                $e->getCode(),
                $e,
            );
        }
    }

    /**
     * @throws SessionException
     */
    public function createUniversalLink(WalletApplication $app, ConnectRequest $rq, string $return = "none"): string
    {
        return $this->createLink($app->universalUrl, $rq, $return);
    }

    /**
     * @throws SessionException
     */
    public function creatDeeplink(ConnectRequest $rq, string $return = "none"): string
    {
        return $this->createLink("tc://", $rq, $return);
    }

    public function pollEvents(Client $sseClient, Cancellation $cancellation): \Generator
    {
        try {
            $url = $this->bridgeUrl . "/events";
            $params = [
                "client_id" => $this->getId(),
            ];

            if ($this->getLastEventId() > 0) {
                $params["last_event_id"] = $this->getLastEventId();
            }

            $url .= "?" . http_build_query($params);

            $this
                ->logger
                ?->debug("SSE url: " . $url);
            $nullEvent = new NullEvent();

            foreach ($sseClient->listen($url, $cancellation) as $event) {
                $sessEv = null;

                if ($event === null) {
                    yield $nullEvent;
                    continue;
                }

                switch ($event->getEventType()) {
                    case "heartbeat":
                        // OK!
                        break;

                    case "message":
                        try {
                            $eventData = $event->getData();

                            if (!empty($eventData)) {
                                $messageData = json_decode($eventData, true, 8, JSON_THROW_ON_ERROR);

                                if (is_array($messageData) && isset($messageData["from"], $messageData["message"])) {
                                    $walletMessage = [];
                                    $clientId = $this->decrypt(
                                        $messageData["from"],
                                        $messageData["message"],
                                        $walletMessage,
                                    );

                                    if ($clientId) {
                                        if (isset($walletMessage["event"])) {
                                            switch ($walletMessage["event"]) {
                                                case "connect":
                                                    if (!$this->clientId) {
                                                        $this->clientId = $clientId;
                                                        $this->lastEventId = (int)$event->getId();
                                                        $sessEv = new Connect($walletMessage, $this);
                                                    }
                                                    break;

                                                default:
                                                    $this
                                                        ->logger
                                                        ?->warning(
                                                            "Unknown event: " . $walletMessage["event"],
                                                            [
                                                                "event_data" => $eventData,
                                                                "wallet_message" => $walletMessage,
                                                            ],
                                                        );
                                                    break;
                                            }
                                        }
                                    }
                                }
                            }
                        } catch (\Throwable $e) {
                            $this
                                ->logger
                                ?->warning("Message error: " . $e->getMessage(), [
                                    "exception" => $e,
                                    "original_event" => (string)$event,
                                ]);
                        }
                        break;

                    default:
                        $this
                            ->logger
                            ?->warning("Unknown SSE event: " . $event->getEventType(), [
                                "original_event" => (string)$event,
                            ]);
                        break;
                }

                yield !$sessEv ? $nullEvent : $sessEv;
            }
        } catch (\Throwable $e) {
            $this
                ->logger
                ?->error("Unhandled exception: " . $e->getMessage(), [
                    "exception" => $e,
                    "bridge" => $this->bridgeUrl,
                ]);
        }
    }

    /**
     * @throws SessionException
     */
    public function sendMessage(HttpMethodsClientInterface $httpClient,
                                SendTransactionRequest $rq,
                                ?string $topic = null,
                                int $ttl = 300,
    ): void
    {
        if (!$this->id || !$this->clientId) {
            throw new SessionException("Invalid session state");
        }

        try {
            $url = $this->bridgeUrl . "/message";
            $params = [
                "client_id" => $this->getId(),
                "to" => $this->getClientId(),
                "ttl" => $ttl,
            ];

            if ($topic !== null) {
                $params["topic"] = $topic;
            }

            $id = $this->lastRequestId + 1;
            $rq = $rq->withId($id);
            $url .= "?" . http_build_query($params);

            $this
                ->logger
                ?->debug(
                    sprintf(
                        "Sending request to %s",
                        $url,
                    ),
                    [
                        "request" => json_encode($rq, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    ],
                );

            $response = $httpClient
                ->post(
                    $url,
                    [
                        "Content-Type" => "text/plain",
                    ],
                    $this->encrypt(json_encode($rq, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)),
                );

            if ($response->getStatusCode() < 200 || $response->getStatusCode() > 299) {
                $mess = "Bad HTTP status code from bridge: " . $response->getStatusCode();
                $this
                    ->logger
                    ?->error(
                        $mess,
                        [
                            "body" => $response->getBody()->getContents(),
                        ]
                    );

                throw new \RuntimeException($mess);
            }
        } catch (\Throwable $e) {
            throw new SessionException($e->getMessage(), $e->getCode(), $e);
        }

        $this->lastRequestId = $id;
    }

    /**
     * @throws \SodiumException
     */
    public function getId(): string
    {
        return sodium_bin2hex($this->id);
    }

    /**
     * @throws \SodiumException
     */
    public function getClientId(): ?string
    {
        return $this->clientId
            ? sodium_bin2hex($this->clientId)
            : null;
    }

    public function getLastEventId(): int
    {
        return $this->lastEventId;
    }

    public function getPreconnectedId(): ?string
    {
        return $this->preconnectedId;
    }

    public function setPreconnectedId(string $preconnectedId): void
    {
        if ($this->preconnectedId) {
            throw new \RuntimeException();
        }

        $this->preconnectedId = $preconnectedId;
    }

    /**
     * @throws SessionException
     */
    protected final function createLink(string $baseUrl, ConnectRequest $rq, string $return = "none"): string
    {
        try {
            $params = [
                "v" => 2,
                "id" => $this->getId(),
                "r" => json_encode($rq, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES),
                "ret" => $return,
            ];
            $query = http_build_query($params);
            $url = parse_url($baseUrl);

            if ($url["scheme"] === "tg" || $url["host"] === "t.me") {
                $baseUrl = str_replace("?attach=wallet", "/start", $baseUrl);

                $q = str_replace('.', '%2E', $query);
                $q = str_replace('-', '%2D', $q);
                $q = str_replace('_', '%5F', $q);
                $q = str_replace('&', '-', $q);
                $q = str_replace('=', '__', $q);
                $q = str_replace('%', '--', $q);

                $query = "startapp=tonconnect-" . $q;
            }

            return $baseUrl . "?" . $query;
        } catch (\Throwable $e) {
            throw new SessionException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @throws \SodiumException
     */
    protected final function decrypt(string $from, string $message, array &$out): ?string
    {
        if (empty($from)) {
            return null;
        }

        $clientId = sodium_hex2bin($from);

        if ($this->clientId !== null && $this->clientId !== $clientId) {
            return null;
        }

        $encrypted = base64_decode($message);
        $decryptionKey = sodium_crypto_box_keypair_from_secretkey_and_publickey($this->secretKey, $clientId);
        $nonce = substr($encrypted, 0, 24);
        $encrypted = substr($encrypted, 24);
        $decrypted = sodium_crypto_box_open($encrypted, $nonce, $decryptionKey);

        if ($decrypted === false) {
            return null;
        }

        try {
            $tmp = json_decode($decrypted, true, flags: JSON_THROW_ON_ERROR);

            if (is_array($tmp)) {
                $out = $tmp;
            }
        } catch (\JsonException $e) {
            $this
                ->logger
                ?->warning("Encrypted JSON message decoding error: " . $e->getMessage(), [
                    "exception" => $e,
                    "decrypted" => $decrypted,
                ]);

            return null;
        }

        return $clientId;
    }

    /**
     * @throws \SodiumException
     */
    protected final function encrypt(string $message): string
    {
        $nonce = random_bytes(24);

        return base64_encode(
            $nonce . sodium_crypto_box(
                $message,
                $nonce,
                sodium_crypto_box_keypair_from_secretkey_and_publickey($this->secretKey, $this->clientId),
            )
        );
    }

    /**
     * @throws \SodiumException
     */
    public function jsonSerialize(): array
    {
        return [
            "client_id" => $this->getClientId(),
            "last_event_id" => $this->getLastEventId(),
            "last_request_id" => $this->lastRequestId,
            "id" => $this->getId(),
            "secret_key" => sodium_bin2hex($this->secretKey),
            "bridge_url" => $this->bridgeUrl,
            "preconnected_id" => $this->preconnectedId,
        ];
    }
}
