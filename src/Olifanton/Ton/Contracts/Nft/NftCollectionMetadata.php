<?php declare(strict_types=1);

namespace Olifanton\Ton\Contracts\Nft;

class NftCollectionMetadata implements \JsonSerializable
{
    /**
     * @param string[] $socialLinks
     */
    public function __construct(
        public readonly string $name,
        public readonly string $description,
        public readonly ?string $image,
        public readonly ?string $coverImage = null,
        public readonly ?string $externalLink = null,
        public readonly array $socialLinks = [],
        public readonly ?string $marketplace = null,
    ) {}

    public function jsonSerialize(): array
    {
        $json = [
            "name" => $this->name,
            "description" => $this->description,
        ];

        if (!empty($this->socialLinks)) {
            $json["social_links"] = $this->socialLinks;
        }

        if ($this->image) {
            $json["image"] = $this->image;
        }

        if ($this->coverImage) {
            $json["cover_image"] = $this->coverImage;
        }

        if ($this->externalLink) {
            $json["external_link"] = $this->externalLink;
        }

        if ($this->marketplace) {
            $json["marketplace"] = $this->marketplace;
        }

        return $json;
    }
}
