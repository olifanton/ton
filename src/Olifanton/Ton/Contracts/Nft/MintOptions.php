<?php declare(strict_types=1);

namespace Olifanton\Ton\Contracts\Nft;

use Brick\Math\BigInteger;
use Olifanton\Interop\Address;

class MintOptions
{
    public function __construct(
        public readonly int $itemIndex,
        public readonly BigInteger $amount,
        public readonly Address $itemOwnerAddress,
        public readonly string $itemContentUrl,
        public readonly int $queryId = 0,
    ) {}
}
