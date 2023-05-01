<?php declare(strict_types=1);

namespace Olifanton\Ton\Contracts\Jetton;

use Brick\Math\BigInteger;
use Olifanton\Interop\Address;
use Olifanton\Interop\Boc\Cell;

class JettonWalletData
{
    public function __construct(
        public readonly BigInteger $balance,
        public readonly ?Address $ownerAddress,
        public readonly ?Address $minterAddress,
        public readonly Cell $walletCode,
    ) {}
}
