<?php declare(strict_types=1);

namespace Olifanton\Ton\Toncenter\Responses;

use Brick\Math\BigInteger;
use Olifanton\Ton\Marshalling\Attributes\JsonMap;

class UnrecognizedSmcRunResult
{
    #[JsonMap("@type")]
    public readonly string $type;

    #[JsonMap("gas_used", serializer: JsonMap::SER_BIGINT)]
    public readonly BigInteger $gasUsed;

    #[JsonMap("exit_code")]
    public readonly int $exitCode;

    #[JsonMap("@extra")]
    public readonly string $extra;

    #[JsonMap]
    public readonly array $stack;
}
