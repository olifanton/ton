<?php declare(strict_types=1);

namespace Olifanton\Ton\Contracts\Wallets\V4;

use Olifanton\Interop\Address;
use Olifanton\Ton\Contracts\Wallets\WalletOptions;
use Olifanton\TypedArrays\Uint8Array;

class WalletV4Options extends WalletOptions
{
    public function __construct(
        Uint8Array $publicKey,
        public readonly int $walletId = 698983191,
        int $workchain = 0,
        ?Address $address = null,
    )
    {
        parent::__construct($publicKey, $workchain, $address);
    }
}
