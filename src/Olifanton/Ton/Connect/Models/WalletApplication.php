<?php declare(strict_types=1);

namespace Olifanton\Ton\Connect\Models;

use Olifanton\Ton\Marshalling\Attributes\JsonMap;
use Olifanton\Ton\Marshalling\Json\Hydrator;

class WalletApplication
{
    #[JsonMap("app_name")]
    public readonly string $appName;

    #[JsonMap("name")]
    public readonly string $name;

    #[JsonMap("image")]
    public readonly string $image;

    #[JsonMap("about_url")]
    public readonly ?string $aboutUrl;

    #[JsonMap("platforms", JsonMap::SER_ARR_OF, "string")]
    public readonly array $platforms;

    #[JsonMap("universal_url")]
    public readonly ?string $universalUrl;

    /** @var Bridge[] */
    #[JsonMap("bridge", JsonMap::SER_ARR_OF, Bridge::class)]
    public readonly array $bridge;

    /**
     * @param array[] $bridge
     * @throws \Olifanton\Ton\Marshalling\Exceptions\MarshallingException
     */
    public static function create(
        string $appName,
        string $name,
        string $image,
        string $universalUrl,
        array $bridge,
        array $platforms = [],
        string $aboutUrl = null,
    ): WalletApplication
    {
        return Hydrator::extract(self::class, [
            "app_name" => $appName,
            "name" => $name,
            "image" => $image,
            "universal_url" => $universalUrl,
            "bridge" => $bridge,
            "platforms" => $platforms,
            "about_url" => $aboutUrl,
        ]);
    }
}
