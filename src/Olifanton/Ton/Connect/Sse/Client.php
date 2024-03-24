<?php declare(strict_types=1);

namespace Olifanton\Ton\Connect\Sse;

use Olifanton\Ton\Connect\Cancellation;
use Olifanton\Ton\Connect\Sse\Exceptions\ConnectionException;

interface Client
{
    /**
     * @param string $url
     * @param Cancellation $cancellation
     * @return \Generator<Event|null>
     * @throws ConnectionException
     */
    public function listen(string $url, Cancellation $cancellation): \Generator;
}
