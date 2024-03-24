<?php declare(strict_types=1);

namespace Olifanton\Ton\Connect\Request;

use Olifanton\Ton\Connect\ConnectItem;

class ConnectRequest implements \JsonSerializable
{
    /**
     * @var ConnectItem[] $items
     */
    private array $items = [];

    public function __construct(
        private readonly string $manifestUrl,
        ConnectItem ...$item,
    )
    {
        $this->items = $item;
        $this->items[] = new ConnectItem("ton_addr");
    }

    public function jsonSerialize(): array
    {
        return [
            "manifestUrl" => $this->manifestUrl,
            "items" => array_map(
                static fn (ConnectItem $item) => $item->jsonSerialize(),
                $this->items,
            ),
        ];
    }
}
