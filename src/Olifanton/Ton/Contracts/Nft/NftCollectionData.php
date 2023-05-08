<?php declare(strict_types=1);

namespace Olifanton\Ton\Contracts\Nft;

use Olifanton\Interop\Address;
use Olifanton\Interop\Boc\Cell;

class NftCollectionData
{
    public function __construct(
        public readonly int $itemsCount,
        public readonly ?Address $ownerAddress,
        public readonly Cell $collectionContentCell,
        public readonly ?string $collectionContentUrl = null,
    ) {}
}
