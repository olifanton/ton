<?php declare(strict_types=1);

namespace Olifanton\Ton\Contracts\Wallets\V4;

use Brick\Math\BigInteger;
use Olifanton\Interop\Address;
use Olifanton\Interop\Boc\Cell;
use Olifanton\Ton\Contracts\Messages\StateInit;

class PluginDeployOptions
{
    public function __construct(
        public readonly Address $dstAddress,
        public readonly int $seqno,
        public int $pluginWc,
        public readonly BigInteger $pluginBalance,
        public readonly StateInit $pluginStateInit,
        public readonly Cell $pluginMsgBody,
        public readonly ?int $expireAt = null,
    ) {}
}
