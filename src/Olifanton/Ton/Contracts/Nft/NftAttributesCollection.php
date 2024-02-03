<?php declare(strict_types=1);

namespace Olifanton\Ton\Contracts\Nft;

class NftAttributesCollection
{
    /**
     * @var array<string, NftAttribute>
     */
    protected array $mappedAttributes = [];

    /**
     * @param NftTrait ...$traits
     */
    public function __construct(
        NftTrait ...$traits,
    )
    {
        foreach ($traits as $trait) {
            $attribute = $trait->asAttribute();
            $this->mappedAttributes[$attribute->traitType] = $attribute;
        }
    }

    /**
     * @param NftTrait|array{type: string, value: string|bool|int|float|null} ...$trait
     * @return NftAttribute[]
     */
    public function forItem(array|NftTrait ...$trait): array
    {
        $result = [];

        foreach ($trait as $aTrait) {
            if ($aTrait instanceof NftTrait) {
                $aTrait = [
                    "type" => $aTrait->traitType,
                    "value" => $aTrait->value,
                ];
            }

            $type = $aTrait["type"];

            if (!isset($this->mappedAttributes[$type])) {
                throw new \RuntimeException(sprintf(
                    "Not found attribute with trait \"%s\" in collection",
                    $type,
                ));
            }

            $result[$type] = $this->mappedAttributes[$type]->withValue($aTrait["value"]);
        }

        if (count($result) !== count($this->mappedAttributes)) {
            foreach ($this->mappedAttributes as $t => $a) {
                if (!isset($result[$t])) {
                    $result[$t] = clone $a;
                }
            }
        }

        return array_values($result);
    }
}
