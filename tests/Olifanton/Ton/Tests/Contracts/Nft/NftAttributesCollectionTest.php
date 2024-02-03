<?php declare(strict_types=1);

namespace Olifanton\Ton\Tests\Contracts\Nft;

use Olifanton\Ton\Contracts\Nft\NftAttribute;
use Olifanton\Ton\Contracts\Nft\NftAttributesCollection;
use Olifanton\Ton\Contracts\Nft\NftTrait;
use PHPUnit\Framework\TestCase;

class NftAttributesCollectionTest extends TestCase
{
    public function testComplex(): void
    {
        // Stubs
        $rarityTrait = new NftTrait("Rarity", "Common");
        $tierTrait = new NftTrait("Tier", 3);
        $colorTrait = new NftTrait("Color", null);

        $attribCollection = new NftAttributesCollection(
            $rarityTrait,
            $tierTrait,
            $colorTrait,
        );

        // Test
        $item0attribs = $attribCollection->forItem(
            $rarityTrait->valued("Legendary"),
            $colorTrait->valued("Red"),
        );
        $this->assertEquals(
            [
                new NftAttribute("Rarity", "Legendary"),
                new NftAttribute("Color", "Red"),
                new NftAttribute("Tier", 3), // Default value
            ],
            $item0attribs,
        );

        $item1attribs = $attribCollection->forItem(
            $tierTrait->valued(1),
        );
        $this->assertEquals(
            [
                new NftAttribute("Tier", 1),
                new NftAttribute("Rarity", "Common"), // Default value
                new NftAttribute("Color", null), // Default value
            ],
            $item1attribs,
        );
    }
}
