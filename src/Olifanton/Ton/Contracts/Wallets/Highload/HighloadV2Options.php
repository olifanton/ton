<?php declare(strict_types=1);

namespace Olifanton\Ton\Contracts\Wallets\Highload;

use Olifanton\Interop\Address;
use Olifanton\Ton\Contracts\Wallets\WalletOptions;
use Olifanton\TypedArrays\Uint8Array;

class HighloadV2Options extends WalletOptions
{
    public function __construct(
        Uint8Array $publicKey,
        public readonly int $subwalletId = 0,
        int $workchain = 0,
        ?Address $address = null,
    )
    {
        parent::__construct($publicKey, $workchain, $address);
    }
}
