<?php declare(strict_types=1);

namespace Olifanton\Ton\Toncenter\Responses;

use Olifanton\Boc\Cell;
use Olifanton\Ton\Marshalling\Attributes\JsonMap;

class AccountState
{
    #[JsonMap("@type")]
    public readonly ?string $type;

    #[JsonMap(serializer: JsonMap::SER_CELL)]
    public readonly ?Cell $code;

    #[JsonMap(serializer: JsonMap::SER_CELL)]
    public readonly ?Cell $data;

    #[JsonMap("frozen_hash")]
    public readonly ?string $frozenHash;

    #[JsonMap("wallet_id")]
    public readonly ?string $walletId;

    #[JsonMap]
    public readonly ?int $seqno;
}
