<?php declare(strict_types=1);

namespace Olifanton\Ton\Toncenter\Responses;

use Brick\Math\BigInteger;
use Olifanton\Ton\Marshalling\Attributes\JsonMap;
use Olifanton\Ton\Models\AddressState;

class WalletInformation
{
    #[JsonMap]
    public readonly bool $wallet;

    #[JsonMap(serializer: JsonMap::SER_BIGINT)]
    public readonly BigInteger $balance;

    #[JsonMap("account_state", JsonMap::SER_ENUM, AddressState::class)]
    public readonly AddressState $accountState;

    #[JsonMap("wallet_type")]
    public readonly ?string $walletType;

    #[JsonMap]
    public readonly ?int $seqno;

    #[JsonMap("last_transaction_id", JsonMap::SER_TYPE, TransactionId::class)]
    public readonly ?TransactionId $lastTransactionId;

    #[JsonMap("wallet_id")]
    public readonly ?int $walletId;
}
