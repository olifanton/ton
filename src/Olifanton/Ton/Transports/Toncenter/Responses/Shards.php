<?php declare(strict_types=1);

namespace Olifanton\Ton\Transports\Toncenter\Responses;

use Olifanton\Ton\Marshalling\Attributes\JsonMap;

class Shards
{
    /**
     * @var BlockIdExt[]
     */
    #[JsonMap(serializer: JsonMap::SER_ARR_OF, param0: BlockIdExt::class)]
    public readonly array $shards;
}
