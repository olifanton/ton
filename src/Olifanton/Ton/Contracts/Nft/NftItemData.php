<?php declare(strict_types=1);

namespace Olifanton\Ton\Contracts\Nft;

use Olifanton\Interop\Address;
use Olifanton\Interop\Boc\Cell;

class NftItemData
{
    public function __construct(
        public readonly bool $isInitialized,
        public readonly int $index,
        public readonly ?Address $collectionAddress,
        public readonly Cell $contentCell,
        public readonly ?string $contentUrl,
        public readonly ?Address $ownerAddress = null,
    ) {}
}
