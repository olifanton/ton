<?php declare(strict_types=1);

namespace Olifanton\Ton\Contracts\Wallets;

use Olifanton\Interop\Address;
use Olifanton\Ton\Contracts\ContractOptions;
use Olifanton\TypedArrays\Uint8Array;

class WalletOptions extends ContractOptions
{
    public function __construct(
        public readonly Uint8Array $publicKey,
        int $workchain = 0,
        ?Address $address = null,
    )
    {
        parent::__construct($workchain, $address);
    }
}
