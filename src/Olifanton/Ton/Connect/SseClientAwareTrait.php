<?php declare(strict_types=1);

namespace Olifanton\Ton\Connect;

use Olifanton\Ton\Connect\Sse\Client;
use Olifanton\Ton\Connect\Sse\StreamClient;
use Psr\Log\LoggerInterface;

trait SseClientAwareTrait
{
    protected ?Client $sseClient = null;

    public function setSseClient(Client $sseClient): void
    {
        $this->sseClient = $sseClient;
    }

    protected function ensureSseClient(): Client
    {
        if ($this->sseClient) {
            return $this->sseClient;
        }

        $this->sseClient = $this->createSseClient();

        return $this->sseClient;
    }

    protected function createSseClient(): Client
    {
        $sseClient = new StreamClient();

        if (property_exists($this, "logger")) {
            if (gettype($this->logger) === "object" && $this->logger instanceof LoggerInterface) {
                $sseClient->setLogger($this->logger);
            }
        }

        return $sseClient;
    }
}
