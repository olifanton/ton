<?php declare(strict_types=1);

namespace Olifanton\Ton\Transports\Toncenter\Responses;

use Brick\Math\BigInteger;
use Olifanton\Interop\Boc\Cell;
use Olifanton\Ton\Marshalling\Attributes\JsonMap;

class Transaction
{
    #[JsonMap]
    public readonly int $utime;

    #[JsonMap(serializer: JsonMap::SER_CELL)]
    public readonly ?Cell $data;

    #[JsonMap("transaction_id", JsonMap::SER_TYPE, TransactionId::class)]
    public readonly ?TransactionId $transactionId;

    #[JsonMap(serializer: JsonMap::SER_BIGINT)]
    public readonly BigInteger $fee;

    #[JsonMap("storage_fee", JsonMap::SER_BIGINT)]
    public readonly BigInteger $storageFee;

    #[JsonMap("other_fee", JsonMap::SER_BIGINT)]
    public readonly BigInteger $otherFee;

    #[JsonMap("in_msg", JsonMap::SER_TYPE, Message::class)]
    public readonly ?Message $inMsg;

    /**
     * @var array<Message>
     */
    #[JsonMap("out_msgs", JsonMap::SER_ARR_OF, Message::class)]
    public readonly array $outMsgs;
}
