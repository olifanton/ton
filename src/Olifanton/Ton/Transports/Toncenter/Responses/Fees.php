<?php declare(strict_types=1);

namespace Olifanton\Ton\Transports\Toncenter\Responses;

use Brick\Math\BigNumber;
use Olifanton\Interop\Units;
use Olifanton\Ton\Marshalling\Attributes\JsonMap;

class Fees
{
    #[JsonMap("@type")]
    public readonly string $type;

    #[JsonMap("in_fwd_fee")]
    public readonly int $inFwdFee;

    #[JsonMap("storage_fee")]
    public readonly int $storageFee;

    #[JsonMap("gas_fee")]
    public readonly int $gasFee;

    #[JsonMap("fwd_fee")]
    public readonly int $fwdFee;

    public function sum(): BigNumber
    {
        return Units::fromNano($this->inFwdFee + $this->storageFee + $this->gasFee + $this->fwdFee);
    }
}
