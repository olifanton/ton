<?php declare(strict_types=1);

namespace Olifanton\Ton\Toncenter\Responses;

use Olifanton\Ton\Marshalling\Attributes\JsonMap;

class BlockTransactions
{
    #[JsonMap(serializer: JsonMap::SER_TYPE, param0: BlockIdExt::class)]
    public readonly BlockIdExt $id;

    #[JsonMap("req_count")]
    public readonly int $reqCount;

    #[JsonMap]
    public readonly bool $incomplete;

    /**
     * @var ShortTxId[]
     */
    #[JsonMap(serializer: JsonMap::SER_ARR_OF, param0: ShortTxId::class)]
    public readonly array $transactions;
}
