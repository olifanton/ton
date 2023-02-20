<?php declare(strict_types=1);

namespace Olifanton\Ton;

use Olifanton\Interop\Boc\Cell;
use Olifanton\Ton\Exceptions\TransportException;
use Olifanton\TypedArrays\Uint8Array;

interface Transport
{
    /**
     * @throws TransportException
     */
    public function runGetMethod(Contract $contract, string $method, array $stack = []): array;

    /**
     * @throws TransportException
     */
    public function send(Cell | Uint8Array | string $boc): void;
}
