<?php declare(strict_types=1);

namespace Olifanton\Ton\Contracts\Nft;

use Olifanton\Interop\Address;
use Olifanton\Interop\Boc\Cell;
use Olifanton\Ton\Contracts\ContractOptions;

class NftCollectionOptions extends ContractOptions
{
    public function __construct(
        public readonly Address $ownerAddress,
        public readonly string $collectionContentUrl,
        public readonly string $nftItemContentBaseUrl,
        public readonly Cell $nftItemCode,
        public readonly float $royalty = 0.0,
        public readonly int $royaltyBase = 1000,
        public readonly ?Cell $code = null,
        public readonly ?Address $royaltyAddress = null,
        ?Address $address = null,
        int $workchain = 0,
    )
    {
        if ($royalty < 0 || $royalty > 1) {
            throw new \InvalidArgumentException("Royalty must be between 0 and 1");
        }
        
        parent::__construct($workchain, $address);
    }
}
