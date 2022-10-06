<?php declare(strict_types=1);

namespace Olifanton\Ton\Toncenter\Responses;

use Brick\Math\BigInteger;
use Olifanton\Ton\Marshalling\Attributes\JsonMap;

class Message
{
    #[JsonMap("@type")]
    public readonly string $type;

    #[JsonMap]
    public readonly string $source;

    #[JsonMap]
    public readonly string $destination;

    #[JsonMap(serializer: JsonMap::SER_BIGINT)]
    public readonly BigInteger $value;

    #[JsonMap("fwd_fee", JsonMap::SER_BIGINT)]
    public readonly BigInteger $fwdFee;

    #[JsonMap("ihr_fee", JsonMap::SER_BIGINT)]
    public readonly BigInteger $ihrFee;

    #[JsonMap("created_lt")]
    public readonly string $createdLt;

    #[JsonMap("body_hash")]
    public readonly string $bodyHash;

    #[JsonMap("msg_data", JsonMap::SER_TYPE, MessageData::class)]
    public readonly MessageData $msgData;

    #[JsonMap]
    public readonly string $message;
}
