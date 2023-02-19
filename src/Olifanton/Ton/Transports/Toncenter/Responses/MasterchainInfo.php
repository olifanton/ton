<?php declare(strict_types=1);

namespace Olifanton\Ton\Transports\Toncenter\Responses;

use Olifanton\Ton\Marshalling\Attributes\JsonMap;

class MasterchainInfo
{
    #[JsonMap(serializer: JsonMap::SER_TYPE, param0: BlockIdExt::class)]
    public readonly BlockIdExt $last;

    #[JsonMap("state_root_hash")]
    public readonly string $stateRootHash;

    #[JsonMap(serializer: JsonMap::SER_TYPE, param0: BlockIdExt::class)]
    public readonly BlockIdExt $init;

    #[JsonMap("@extra")]
    public readonly string $extra;
}
