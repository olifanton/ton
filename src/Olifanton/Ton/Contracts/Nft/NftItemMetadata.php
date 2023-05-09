<?php declare(strict_types=1);

namespace Olifanton\Ton\Contracts\Nft;

class NftItemMetadata implements \JsonSerializable
{
    /**
     * @param array<array{trait_type: string, value: string}|NftAttribute> $attributes
     */
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
            "attributes" => array_map(
                fn(NftAttribute|array $attribute) => ($attribute instanceof NftAttribute)
                    ? $attribute->jsonSerialize()
                    : $this->validateAttribute($attribute),
                $this->attributes,
            ),
        ];

        if ($this->image) {
            $json["image"] = $this->image;
        }

        if ($this->imageData) {
            $json["image_data"] = $this->imageData;
        }

        return $json;
    }

    protected function validateAttribute(array $attribute): array
    {
        if (!isset($attribute["trait_type"])) {
            throw new \InvalidArgumentException("`trait_type` is required");
        }

        if (!isset($attribute["value"])) {
            throw new \InvalidArgumentException("`value` is required");
        }

        return $attribute;
    }
}
