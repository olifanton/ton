<?php declare(strict_types=1);

namespace Olifanton\Ton\Contracts\Nft;

class NftAttribute implements \JsonSerializable
{
    public function __construct(
        public readonly string $traitType,
        public readonly string $value,
    ) {}

    public function jsonSerialize(): array
    {
        return [
            "trait_type" => $this->traitType,
            "value" => $this->value,
        ];
    }
}
