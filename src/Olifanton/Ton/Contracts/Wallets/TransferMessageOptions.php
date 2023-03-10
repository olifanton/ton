<?php declare(strict_types=1);

namespace Olifanton\Ton\Contracts\Wallets;

use Brick\Math\BigInteger;
use Olifanton\Interop\Address;
use Olifanton\Interop\Boc\Cell;
use Olifanton\Ton\Contracts\Messages\StateInit;

class TransferMessageOptions
{
    public function __construct(
        public readonly Address $dest,
        public readonly BigInteger $amount,
        public readonly int $seqno,
        public readonly string | Cell $payload = "",
        public readonly int $sendMode = 3, // @TODO: To `SendMode` enumeration
        public readonly ?StateInit $stateInit = null,
    )
    {
    }
}
