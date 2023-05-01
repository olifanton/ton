<?php declare(strict_types=1);

namespace Olifanton\Ton\Contracts\Wallets;

use Olifanton\Ton\SendMode;

class TransferOptions
{
    public function __construct(
        public readonly ?int $seqno = null,
        public readonly int $timeout = 60,
        public readonly SendMode|int $sendMode = SendMode::NONE,
    ) {}
}
