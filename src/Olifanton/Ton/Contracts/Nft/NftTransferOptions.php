<?php declare(strict_types=1);

namespace Olifanton\Ton\Contracts\Nft;

use Brick\Math\BigInteger;
use Olifanton\Interop\Address;
use Olifanton\TypedArrays\Uint8Array;

class NftTransferOptions
{
    public function __construct(
        public readonly Address $newOwnerAddress,
        public readonly ?Address $responseAddress = null,
        public readonly ?BigInteger $forwardAmount = null,
        public readonly ?Uint8Array $forwardPayload = null,
        public readonly int $queryId = 0,
    ) {}
}
