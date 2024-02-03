<?php declare(strict_types=1);

namespace Olifanton\Ton\Contracts\Nft;

class NftAttribute implements \JsonSerializable
{
    public function __construct(
        public readonly string $traitType,
        public readonly string|bool|int|float|null $value = null,
    ) {}

    public function jsonSerialize(): array
    {
        return [
            "trait_type" => $this->traitType,
            "value" => $this->value,
        ];
    }

    public function withValue(string|bool|int|float|null $value): self
    {
        return new self(
            $this->traitType,
            $value,
        );
    }
}
