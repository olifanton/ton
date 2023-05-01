<?php declare(strict_types=1);

namespace Olifanton\Ton\Contracts\Jetton;

use Olifanton\Interop\Address;
use Olifanton\Interop\Boc\Cell;
use Olifanton\Ton\Contracts\ContractOptions;
use Olifanton\TypedArrays\Uint8Array;

class JettonMinterOptions extends ContractOptions
{
    public function __construct(
        public readonly ?Address $adminAddress,
        public readonly string $jettonContentUrl,
        public readonly Cell $jettonWalletCode,
        public readonly ?Address $address = null,
        public readonly ?Cell $code = null,
        int $workchain = 0,
        ?Uint8Array $publicKey = null,
    )
    {
        parent::__construct($publicKey, $workchain);
    }
}
