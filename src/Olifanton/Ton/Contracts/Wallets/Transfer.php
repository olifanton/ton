<?php declare(strict_types=1);

namespace Olifanton\Ton\Contracts\Wallets;

use Brick\Math\BigInteger;
use Olifanton\Interop\Address;
use Olifanton\Interop\Boc\Cell;
use Olifanton\Ton\Contracts\Messages\StateInit;
use Olifanton\Ton\SendMode;

class Transfer
{
    public function __construct(
        public readonly Address $dest,
        public readonly BigInteger $amount,
        public readonly string|Cell $payload = "",
        public readonly SendMode|int $sendMode = SendMode::NONE,
        public readonly ?StateInit $stateInit = null,
    ) {}
}
