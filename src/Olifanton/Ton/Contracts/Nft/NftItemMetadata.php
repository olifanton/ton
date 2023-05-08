<?php declare(strict_types=1);

namespace Olifanton\Ton\Contracts\Nft;

class NftItemMetadata implements \JsonSerializable
{
    public function __construct(
        public readonly string $name,
        public readonly string $description,
        public readonly ?string $image,
        public readonly ?string $imageData = null,
        public readonly array $attributes = [],
    ) {}

    public function jsonSerialize(): array
    {
        $json = [
            "name" => $this->name,
            "description" => $this->description,
            "attributes" => $this->attributes,
        ];

        if ($this->image) {
            $json["image"] = $this->image;
        }

        if ($this->imageData) {
            $json["image_data"] = $this->imageData;
        }

        return $json;
    }
}
