<?php declare(strict_types=1);

namespace Olifanton\Ton\Connect\Sse;

use Olifanton\Ton\Connect\Cancellation;
use Olifanton\Ton\Connect\Sse\Exceptions\ConnectionException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class StreamClient implements Client, LoggerAwareInterface
{
    use LoggerAwareTrait;

    private ?string $lastEventId = null;

    public function __construct(
        private readonly float $timeout = 60.0,
        private readonly bool $ignoreSslErrors = false,
    ) {}

    /**
     * @param callable(Event): void $onMessage
     * @throws ConnectionException
     */
    public function listen(string $url, callable $onMessage, Cancellation $cancellation): void
    {
        $stream = $this->connect($url);

        try {
            $buffer = "";

            while (!$cancellation->isCanceled()) {
                if (feof($stream)) {
                    $this->close($stream);
                    usleep(500_000);
                    $stream = $this->connect($url);
                    $buffer = "";
                }

                $buffer .= fread($stream, 1);

                if (preg_match("/\r\n\r\n|\n\n|\r\r/", $buffer)) {
                    $parts = preg_split(
                        "/\r\n\r\n|\n\n|\r\r/",
                        $buffer,
                        2,
                    );

                    $rawMessage = $parts[0];
                    $remaining = $parts[1];
                    $buffer = $remaining;

                    try {
                        $event = Event::parse($rawMessage);
                    } catch (\Throwable $e) {
                        $this
                            ->logger
                            ?->warning(
                                "[SSE Client] Event parsing error: " . $e->getMessage(),
                                [
                                    "exception" => $e,
                                    "raw" => $rawMessage,
                                ],
                            );

                        continue;
                    }

                    if ($event->getId()) {
                        $this->lastEventId = $event->getId();
                    }

                    call_user_func_array($onMessage, [$event]);
                }
            }
        } finally {
            $this->close($stream);
        }
    }

    /**
     * @return resource
     * @throws ConnectionException
     */
    protected function connect(string $url): mixed
    {
        $headers = [
            "Connection: keep-alive",
            "Cache-Control: no-cache",
        ];

        if ($this->lastEventId) {
            $headers[] = "Last-Event-ID: " . $this->lastEventId;
        }

        $options = [
            "http" => [
                "method" => "GET",
                "header" => implode("\r\n", $headers),
                "timeout" => (int)($this->timeout * 1000),
            ],
        ];

        if ($this->ignoreSslErrors) {
            $options["ssl"] = [
                "verify_peer" => false,
                "verify_peer_name" => false,
            ];
        }

        $context = stream_context_create($options);
        $stream = fopen($url, "r", false, $context);

        if (!$stream) {
            $lastErr = error_get_last();
            $message = "Unable to connect SSE server";
            $code = 0;

            if (isset($lastErr["message"])) {
                $message .= ": " . $lastErr["message"];
                $code = (int)$lastErr["type"];
            }

            throw new ConnectionException($message, $code);
        }

        return $stream;
    }

    protected function close($stream): void
    {
        if (is_resource($stream)) {
            fclose($stream);
        }
    }
}
