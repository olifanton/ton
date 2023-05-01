<?php declare(strict_types=1);

namespace Olifanton\Ton\Contracts\Wallets\Highload;

use Olifanton\Ton\Contracts\ContractOptions;
use Olifanton\TypedArrays\Uint8Array;

class HighloadV2Options extends ContractOptions
{
    public function __construct(Uint8Array $publicKey,
                                int $workchain = 0,
                                public readonly int $subwalletId = 0,
    )
    {
        parent::__construct($publicKey, $workchain);
    }
}
