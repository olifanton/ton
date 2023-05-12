<?php declare(strict_types=1);

namespace Olifanton\Ton\Contracts\Wallets;

class TransferOptions
{
    public function __construct(
        public readonly ?int $seqno = null,
        public readonly int $timeout = 60,
    ) {}
}
