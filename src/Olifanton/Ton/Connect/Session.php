<?php declare(strict_types=1);

namespace Olifanton\Ton\Connect;

use Olifanton\Interop\Bytes;
use Olifanton\Ton\Connect\Models\Bridge;
use Olifanton\Ton\Connect\Models\BridgeType;

class Session
{
    private readonly string $id;

    private readonly string $secretKey;

    private ?string $clientId = null;

    private ?string $bridgeUrl = null;

    private int $lastEventId = 0;

    private int $lastRequestId = 1;

    /**
     * @throws \SodiumException
     */
    public function __construct()
    {
        $kp = sodium_crypto_box_keypair();
        $this->id = sodium_crypto_box_publickey($kp);
        $this->secretKey = sodium_crypto_box_secretkey($kp);
    }

    public function connect(Bridge $bridge, callable $onMessage): Cancellation
    {
        if ($bridge->type !== BridgeType::SSE) {
            throw new \RuntimeException("Only SSE bridge currently supported");
        }

        $url = $bridge->url . "/events";
        $params = [
            "client_id" => Bytes::bytesToHexString(Bytes::bytesToArray($this->id)),
        ];

        if ($this->getLastEventId() > 0) {
            $params["last_event_id"] = $this->getLastEventId();
        }

        $url .= "?" . http_build_query($params);


    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getSecretKey(): string
    {
        return $this->secretKey;
    }

    public function getClientId(): ?string
    {
        return $this->clientId;
    }

    public function getBridgeUrl(): ?string
    {
        return $this->bridgeUrl;
    }

    public function getLastEventId(): int
    {
        return $this->lastEventId;
    }

    public function getLastRequestId(): int
    {
        return $this->lastRequestId;
    }

    public function setClientId(string $clientId): void
    {
        $this->clientId = $clientId;
    }

    public function setBridgeUrl(string $bridgeUrl): void
    {
        $this->bridgeUrl = $bridgeUrl;
    }

    public function setLastEventId(int $lastEventId): void
    {
        $this->lastEventId = $lastEventId;
    }

    public function setLastRequestId(int $lastRequestId): void
    {
        $this->lastRequestId = $lastRequestId;
    }
}
