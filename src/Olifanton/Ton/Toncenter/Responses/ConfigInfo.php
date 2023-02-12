<?php declare(strict_types=1);

namespace Olifanton\Ton\Toncenter\Responses;

use Olifanton\Ton\Marshalling\Attributes\JsonMap;

class ConfigInfo
{
    #[JsonMap("@type")]
    public readonly string $type;

    #[JsonMap]
    public readonly string $bytes;
}
