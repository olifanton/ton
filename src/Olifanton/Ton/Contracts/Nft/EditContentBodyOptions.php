<?php declare(strict_types=1);

namespace Olifanton\Ton\Contracts\Nft;

use Olifanton\Interop\Address;

class EditContentBodyOptions
{
    public function __construct(
        public readonly string $collectionContentUrl,
        public readonly string $nftItemContentBaseUri,
        public readonly float $royalty = 0.0,
        public readonly ?Address $royaltyAddress = null,
        public readonly int $royaltyBase = 1000,
        public readonly int $queryId = 0,
    ) {
        if ($royalty < 0 || $royalty > 1) {
            throw new \InvalidArgumentException("Royalty must be between 0 and 1");
        }
    }
}
