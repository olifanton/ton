<?php declare(strict_types=1);

namespace Olifanton\Ton\Contracts\Jetton;

use Brick\Math\BigInteger;
use Olifanton\Interop\Address;

class MintOptions
{
    public function __construct(
        public readonly BigInteger $jettonAmount,
        public readonly Address $destination,
        public readonly BigInteger $amount,
        public readonly int $queryId = 0,
    ) {}
}
