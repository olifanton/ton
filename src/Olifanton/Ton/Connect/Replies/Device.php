<?php declare(strict_types=1);

namespace Olifanton\Ton\Connect\Replies;

use Olifanton\Ton\Marshalling\Attributes\JsonMap;
use Olifanton\Ton\Marshalling\Json\Hydrator;

class Device implements \JsonSerializable
{
    #[JsonMap("platform")]
    public readonly string $platform;

    #[JsonMap("appName")]
    public readonly string $appName;

    #[JsonMap("appVersion")]
    public readonly string $appVersion;

    #[JsonMap("maxProtocolVersion", JsonMap::SER_TYPE, "int")]
    public readonly int $maxProtocolVersion;

    #[JsonMap("features")]
    public readonly array $features;

    public function hasFeature(string $featureName): bool
    {
        return self::hasFeatureArray($featureName, $this->features);
    }

    public function isSendTransactionSupported(): bool
    {
        return $this->hasFeature("SendTransaction");
    }

    public static function hasFeatureArray(string $featureName, array $features): bool
    {
        if (in_array($featureName, $features)) {
            return true;
        }

        foreach ($features as $feature) {
            if (is_array($feature)) {
                if (isset($feature["name"]) && $feature["name"] === $featureName) {
                    return true;
                }
            }
        }

        return false;
    }

    public function jsonSerialize(): array
    {
        return [
            "platform" => $this->platform,
            "appName" => $this->appName,
            "appVersion" => $this->appVersion,
            "maxProtocolVersion" => $this->maxProtocolVersion,
            "features" => $this->features,
        ];
    }

    /**
     * @throws \JsonException
     * @throws \Olifanton\Ton\Marshalling\Exceptions\MarshallingException
     */
    public static function restore(array|string $json): self
    {
        return Hydrator::extract(
            self::class,
            is_string($json) ? json_decode($json, true, flags: JSON_THROW_ON_ERROR) : $json,
        );
    }
}
