<?php declare(strict_types=1);

namespace Olifanton\Ton\Toncenter\Responses;

use Olifanton\Ton\Marshalling\Attributes\JsonMap;

class TransactionsList
{
    /**
     * @var array<Transaction>
     */
    #[JsonMap("items", JsonMap::SER_ARR_OF, Transaction::class)]
    public readonly array $items;
}
