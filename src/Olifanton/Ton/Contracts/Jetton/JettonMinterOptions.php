<?php declare(strict_types=1);

namespace Olifanton\Ton\Contracts\Jetton;

use Olifanton\Interop\Address;
use Olifanton\Interop\Boc\Cell;
use Olifanton\Ton\Contracts\ContractOptions;

class JettonMinterOptions extends ContractOptions
{
    public function __construct(
        public readonly ?Address $adminAddress,
        public readonly string $jettonContentUrl,
        public readonly Cell $jettonWalletCode,
        public readonly ?Cell $code = null,
        ?Address $address = null,
        int $workchain = 0,
    )
    {
        parent::__construct($workchain, $address);
    }
}
