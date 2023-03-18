<?php declare(strict_types=1);

namespace Olifanton\Ton\Transports\Toncenter\Responses;

use Olifanton\Ton\Marshalling\Attributes\JsonMap;

class QueryFees
{
    #[JsonMap("@type")]
    public readonly string $type;

    #[JsonMap("source_fees", JsonMap::SER_TYPE, Fees::class)]
    public readonly Fees $sourceFees;

    #[JsonMap("destination_fees", JsonMap::SER_TYPE, Fees::class)]
    public readonly ?Fees $destinationFees;
}
