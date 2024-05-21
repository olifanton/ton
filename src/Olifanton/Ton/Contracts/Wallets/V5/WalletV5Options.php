<?php declare(strict_types=1);

namespace Olifanton\Ton\Contracts\Wallets\V5;

use Olifanton\Interop\Address;
use Olifanton\Ton\Contracts\Wallets\WalletOptions;
use Olifanton\Ton\Contracts\Wallets\WalletId;
use Olifanton\TypedArrays\Uint8Array;

class WalletV5Options extends WalletOptions
{
    public function __construct(
        Uint8Array                $publicKey,
        public readonly ?WalletId $walletId = null,
        int                       $workchain = 0,
        ?Address                  $address = null,
    )
    {
        parent::__construct($publicKey, $workchain, $address);
    }
}
