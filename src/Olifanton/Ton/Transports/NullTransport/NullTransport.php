<?php declare(strict_types=1);

namespace Olifanton\Ton\Transports\NullTransport;

use Olifanton\Interop\Boc\Cell;
use Olifanton\Ton\Contract;
use Olifanton\Ton\Contracts\Exceptions\ContractException;
use Olifanton\Ton\Exceptions\TransportException;
use Olifanton\Ton\Transport;
use Olifanton\TypedArrays\Uint8Array;

class NullTransport implements Transport
{
    public function runGetMethod(Contract $contract, string $method, array $stack = []): array
    {
        try {
            $contract->getAddress();
        } catch (ContractException $e) {
            throw new TransportException(
                "Address error: " . $e->getMessage(),
                0,
                $e,
            );
        }

        return [];
    }

    public function send(Uint8Array | string | Cell $boc): void
    {
        // Nothing
    }
}
