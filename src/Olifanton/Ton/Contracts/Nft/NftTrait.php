<?php declare(strict_types=1);

namespace Olifanton\Ton\Contracts\Nft;

class NftTrait
{
    public function __construct(
        public readonly string $traitType,
        public readonly string|bool|int|float|null $value,
    ) {}

    public function valued(string|bool|int|float|null $value): array
    {
        return [
            "type" => $this->traitType,
            "value" => $value,
        ];
    }

    public function asAttribute(): NftAttribute
    {
        return new NftAttribute($this->traitType, $this->value);
    }
}
