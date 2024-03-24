<?php declare(strict_types=1);

namespace Olifanton\Ton\Connect\Models;

use Olifanton\Ton\Marshalling\Attributes\JsonMap;
use Olifanton\Ton\Marshalling\Json\Hydrator;

class Bridge
{
    #[JsonMap("type", JsonMap::SER_ENUM, BridgeType::class)]
    public readonly BridgeType $type;

    #[JsonMap("url")]
    public readonly ?string $url;

    #[JsonMap("key")]
    public readonly ?string $key;

    /**
     * @throws \Olifanton\Ton\Marshalling\Exceptions\MarshallingException
     */
    public static function createSSE(string $url): self
    {
        return Hydrator::extract(self::class, [
            "type" => BridgeType::SSE->value,
            "url" => $url,
        ]);
    }
}
