<?php declare(strict_types=1);

namespace Olifanton\Ton\Contracts\Jetton;

use Brick\Math\BigInteger;
use Olifanton\Interop\Address;
use Olifanton\Interop\Boc\Cell;

class JettonData
{
    public function __construct(
        public readonly BigInteger $totalSupply,
        public readonly bool $isMutable,
        public readonly ?Address $adminAddress,
        public readonly ?string $jettonContentUrl,
        public readonly ?Cell $jettonContentCell,
        public readonly ?Cell $jettonWalletCode,
    ) {}
}
