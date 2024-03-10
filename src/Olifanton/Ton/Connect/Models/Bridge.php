<?php declare(strict_types=1);

namespace Olifanton\Ton\Connect\Models;

use Olifanton\Ton\Marshalling\Attributes\JsonMap;

class Bridge
{
    #[JsonMap("type", JsonMap::SER_ENUM, BridgeType::class)]
    public readonly BridgeType $type;

    #[JsonMap("url")]
    public readonly ?string $url;

    #[JsonMap("key")]
    public readonly ?string $key;
}
