<?php declare(strict_types=1);

namespace Olifanton\Ton\Toncenter\Responses;

use Olifanton\Ton\Marshalling\Attributes\JsonMap;

class BlockIdExt
{
    #[JsonMap]
    public readonly int $workchain;

    #[JsonMap]
    public readonly string $shard;

    #[JsonMap]
    public readonly int $seqno;

    #[JsonMap("root_hash")]
    public readonly string $rootHash;

    #[JsonMap("file_hash")]
    public readonly string $fileHash;
}
