<?php declare(strict_types=1);

namespace Olifanton\Ton\Contracts\Wallets\V3;

use Olifanton\Ton\Contracts\ContractOptions;
use Olifanton\TypedArrays\Uint8Array;

class WalletV3Options extends ContractOptions
{
    public function __construct(Uint8Array $publicKey,
                                int $workchain = 0,
                                public readonly int $walletId = 698983191)
    {
        parent::__construct($publicKey, $workchain);
    }
}
