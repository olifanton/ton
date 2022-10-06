<?php declare(strict_types=1);

namespace Olifanton\Ton\Toncenter\Responses;

use Brick\Math\BigInteger;
use Olifanton\Ton\Marshalling\Attributes\JsonMap;

class ExtendedFullAccountState
{
    #[JsonMap("address.account_address")]
    public readonly string $address;

    #[JsonMap(serializer: JsonMap::SER_BIGINT)]
    public readonly BigInteger $balance;

    #[JsonMap("last_transaction_id", JsonMap::SER_TYPE, TransactionId::class)]
    public readonly ?TransactionId $lastTransactionId;

    #[JsonMap("block_id", JsonMap::SER_TYPE, BlockIdExt::class)]
    public readonly ?BlockIdExt $blockId;

    #[JsonMap("sync_utime")]
    public readonly int $syncUtime;

    #[JsonMap]
    public readonly int $revision;

    #[JsonMap("account_state", JsonMap::SER_TYPE, AccountState::class)]
    public readonly ?AccountState $accountState;
}
