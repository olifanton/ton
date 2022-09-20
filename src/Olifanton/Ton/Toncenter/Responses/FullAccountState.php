<?php declare(strict_types=1);

namespace Olifanton\Ton\Toncenter\Responses;

use Brick\Math\BigInteger;
use Olifanton\Boc\Cell;
use Olifanton\Ton\Marshalling\Attributes\JsonMap;

class FullAccountState
{
    #[JsonMap(serializer: JsonMap::SER_BIGINT)]
    public readonly BigInteger $balance;

    #[JsonMap(serializer: JsonMap::SER_CELL)]
    public readonly ?Cell $code;

    #[JsonMap(serializer: JsonMap::SER_CELL)]
    public readonly ?Cell $data;

    #[JsonMap("last_transaction_id", JsonMap::SER_TYPE, LastTransactionId::class)]
    public readonly ?LastTransactionId $lastTransactionId;

    #[JsonMap("block_id", JsonMap::SER_TYPE, BlockIdExt::class)]
    public readonly ?BlockIdExt $blockId;

    #[JsonMap("frozen_hash")]
    public readonly string $frozenHash;

    #[JsonMap("sync_utime")]
    public readonly int $syncUtime;

    #[JsonMap]
    public readonly string $state;
}
