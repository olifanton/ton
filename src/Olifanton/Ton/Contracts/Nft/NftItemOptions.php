<?php declare(strict_types=1);

namespace Olifanton\Ton\Contracts\Nft;

use Olifanton\Interop\Address;
use Olifanton\Interop\Boc\Cell;
use Olifanton\Ton\Contracts\ContractOptions;

class NftItemOptions extends ContractOptions
{
    public function __construct(
        public readonly int $index,
        public readonly Address $collectionAddress,
        public readonly ?Cell $code = null,
        ?Address $address = null,
        int $workchain = 0,
    )
    {
        parent::__construct($workchain, $address);
    }
}
