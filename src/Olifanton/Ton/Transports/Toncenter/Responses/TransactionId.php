<?php declare(strict_types=1);

namespace Olifanton\Ton\Transports\Toncenter\Responses;

use Olifanton\Ton\Marshalling\Attributes\JsonMap;

class TransactionId
{
    #[JsonMap]
    public readonly string $lt;

    #[JsonMap]
    public readonly string $hash;
}
