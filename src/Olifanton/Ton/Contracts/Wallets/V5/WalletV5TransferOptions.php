<?php declare(strict_types=1);

namespace Olifanton\Ton\Contracts\Wallets\V5;

use Olifanton\Ton\Contracts\Wallets\TransferOptions;

class WalletV5TransferOptions extends TransferOptions
{
    public function __construct(
        ?int $seqno = null,
        int $timeout = 60,
    )
    {
        parent::__construct($seqno, $timeout);
    }
}
