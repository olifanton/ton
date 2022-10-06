<?php declare(strict_types=1);

namespace Olifanton\Ton\Toncenter\Responses;

use Olifanton\Ton\Marshalling\Attributes\JsonMap;

class MessageData
{
    #[JsonMap("@type")]
    public readonly string $type;

    #[JsonMap]
    public readonly string $body;

    #[JsonMap("init_state")]
    public readonly string $initState;
}
