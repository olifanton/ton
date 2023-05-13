<?php declare(strict_types=1);

namespace Olifanton\Ton\Contracts\Wallets;

class TransferOptions
{
    /**
     * @param int|null $seqno Seqno. Set `0` to initialize wallet when making transfers
     */
    public function __construct(
        public readonly ?int $seqno = null,
        public readonly int $timeout = 60,
    ) {}
}
