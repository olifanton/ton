<?php declare(strict_types=1);

namespace Olifanton\Ton\Connect\Sse;

use Olifanton\Ton\Connect\Cancellation;
use Olifanton\Ton\Connect\Sse\Exceptions\ConnectionException;

interface Client
{
    /**
     * @throws ConnectionException
     */
    public function listen(string $url, callable $onMessage, Cancellation $cancellation): void;
}
