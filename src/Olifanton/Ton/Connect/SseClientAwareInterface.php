<?php declare(strict_types=1);

namespace Olifanton\Ton\Connect;

use Olifanton\Ton\Connect\Sse\Client;

interface SseClientAwareInterface
{
    public function setSseClient(Client $sseClient): void;
}
