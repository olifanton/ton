<?php declare(strict_types=1);

namespace Olifanton\Ton\Contracts\Jetton;

class JettonMetadata implements \JsonSerializable
{
    public function __construct(
        public readonly string $name,
        public readonly string $description,
        public readonly string $symbol,
        public readonly string $imageData,
        public readonly int $decimals = 9,
    ) {}

    public function jsonSerialize(): array
    {
        return [
            "name" => $this->name,
            "description" => $this->description,
            "symbol" => $this->symbol,
            "image_data" => $this->imageData,
            "decimals" => $this->decimals,
        ];
    }

    /**
     * @param array{name: string, description: string, symbol: string, image_data: string, decimals: int|string}|string $json
     * @throws \JsonException
     */
    public static function fromJson(array|string $json): self
    {
        if (is_string($json)) {
            $json = json_decode($json, true, flags: JSON_THROW_ON_ERROR);
        }

        $name = $json["name"] ?? throw new \InvalidArgumentException("`name` is required");
        $description = $json["description"] ?? throw new \InvalidArgumentException("`description` is required");
        $symbol = $json["symbol"] ?? throw new \InvalidArgumentException("`symbol` is required");
        $imageData = $json["image_data"] ?? throw new \InvalidArgumentException("`image_data` is required");
        $decimals = $json["decimals"] ?? throw new \InvalidArgumentException("`decimals` is required");

        return new self(
            $name,
            $description,
            $symbol,
            $imageData,
            (int)$decimals,
        );
    }
}
