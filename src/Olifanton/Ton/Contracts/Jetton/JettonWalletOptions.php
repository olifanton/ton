<?php declare(strict_types=1);

namespace Olifanton\Ton\Contracts\Jetton;

use Olifanton\Interop\Address;
use Olifanton\Interop\Boc\Cell;
use Olifanton\Ton\Contracts\ContractOptions;

class JettonWalletOptions extends ContractOptions
{
    public function __construct(
        public readonly ?Cell $code = null,
        int $workchain = 0,
        ?Address $address = null,
    )
    {
        parent::__construct($workchain, $address);
    }
}
