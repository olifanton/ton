<?php declare(strict_types=1);

namespace Olifanton\Ton\Transports\NullTransport;

use Brick\Math\BigInteger;
use Brick\Math\BigNumber;
use Olifanton\Interop\Address;
use Olifanton\Interop\Boc\Cell;
use Olifanton\Ton\Contract;
use Olifanton\Ton\Contracts\Exceptions\ContractException;
use Olifanton\Ton\Contracts\Messages\ExternalMessage;
use Olifanton\Ton\Contracts\Messages\ResponseStack;
use Olifanton\Ton\Exceptions\TransportException;
use Olifanton\Ton\Transport;
use Olifanton\TypedArrays\Uint8Array;

class NullTransport implements Transport
{
    public function runGetMethod(Contract $contract, string $method, array $stack = []): ResponseStack
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

        return ResponseStack::empty();
    }

    public function send(Uint8Array | string | Cell $boc): void
    {
        // Nothing
    }

    public function sendMessage(ExternalMessage $message, Uint8Array $secretKey): void
    {
        // Nothing
    }

    public function estimateFee(Address $address,
                                Cell | Uint8Array | string $body,
                                Cell | Uint8Array | string | null $initCode = null,
                                Cell | Uint8Array | string | null $initData = null): BigNumber
    {
        return BigNumber::of(0);
    }
}
