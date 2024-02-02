<?php declare(strict_types=1);

namespace Olifanton\Ton\Contracts\Jetton;

use Brick\Math\BigInteger;
use Olifanton\Interop\Address;
use Olifanton\TypedArrays\Uint8Array;

class TransferJettonOptions
{
    public function __construct(
        public readonly BigInteger $jettonAmount,
        public readonly Address $toAddress,
        public readonly ?Address $responseAddress,
        public readonly int $queryId = 0,
        public readonly ?Uint8Array $forwardPayload = null,
        public readonly ?BigInteger $forwardAmount = null,
    ) {}
}
