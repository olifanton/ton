<?php declare(strict_types=1);

namespace Olifanton\Ton\Contracts\Wallets\V4;

use Brick\Math\BigInteger;
use Olifanton\Interop\Address;

class PlugOptions
{
    public function __construct(
        public readonly int $seqno,
        public readonly Address $pluginAddress,
        public readonly BigInteger $amount,
        public readonly ?int $queryId = null,
        public readonly ?int $expireAt = null,
    ) {}
}
