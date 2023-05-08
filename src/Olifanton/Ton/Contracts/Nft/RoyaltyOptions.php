<?php declare(strict_types=1);

namespace Olifanton\Ton\Contracts\Nft;

use Olifanton\Interop\Address;

class RoyaltyOptions
{
    public function __construct(
        public readonly float $royalty = 0.0,
        public readonly int $royaltyBase = 1000,
        public readonly ?Address $royaltyAddress = null,
    ) {}
}
