<?php declare(strict_types=1);

namespace Olifanton\Ton\Toncenter\Responses;

use Olifanton\Ton\Marshalling\Attributes\JsonMap;

class ShortTxId
{
    #[JsonMap]
    public readonly int $mode;

    #[JsonMap]
    public readonly string $account;

    #[JsonMap]
    public readonly string $lt;

    #[JsonMap]
    public readonly string $hash;
}
