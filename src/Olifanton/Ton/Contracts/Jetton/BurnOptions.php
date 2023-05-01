<?php declare(strict_types=1);

namespace Olifanton\Ton\Contracts\Jetton;

use Brick\Math\BigInteger;
use Olifanton\Interop\Address;

class BurnOptions
{
    public function __construct(
        public readonly BigInteger $jettonAmount,
        public readonly ?Address $responseAddress,
        public readonly int $queryId = 0,
    ) {}
}
