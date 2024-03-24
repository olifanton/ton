<?php declare(strict_types=1);

namespace Olifanton\Ton\Connect\Sse;

use Olifanton\Ton\Connect\Cancellation;
use Olifanton\Ton\Connect\Sse\Exceptions\ConnectionException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class StreamClient implements Client, LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private readonly float $timeout = 60.0,
        private readonly bool $ignoreSslErrors = false,
    ) {}

    public function listen(string $url, Cancellation $cancellation): \Generator
    {
        $stream = $this->connect($url);

        try {
            $buffer = "";

            while (!$cancellation->isCanceled()) {
                if (feof($stream)) {
                    $this->close($stream);
                    usleep(500_000);
                    $this
                        ->logger
                        ?->debug("Reconnect...");
                    $stream = $this->connect($url);
                    $buffer = "";
                }

                $buffer .= fread($stream, 1);
                $event = null;

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
                }

                yield $event;
            }

            $this
                ->logger
                ?->debug("Cancellation signal received");
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
            $message = "Unable to connect to SSE server";
            $code = 0;

            if (isset($lastErr["message"])) {
                $message .= ": " . $lastErr["message"];
                $code = (int)$lastErr["type"];
            }

            throw new ConnectionException($message, $code);
        }

        stream_set_blocking($stream, false);

        $this
            ->logger
            ?->debug("SSE channel connected, URL: " . $url);

        return $stream;
    }

    protected function close($stream): void
    {
        if (is_resource($stream)) {
            fclose($stream);
        }
    }
}
