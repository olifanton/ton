<?php declare(strict_types=1);

namespace Olifanton\Ton;

use Brick\Math\BigInteger;
use Olifanton\Ton\Contracts\Wallets\Wallet;
use Olifanton\TypedArrays\Uint8Array;

class DeployOptions
{
    public function __construct(
        public readonly Wallet     $deployerWallet,
        public readonly Uint8Array $deployerSecretKey,
        public readonly BigInteger $storageAmount,
    )
    {
    }
}
