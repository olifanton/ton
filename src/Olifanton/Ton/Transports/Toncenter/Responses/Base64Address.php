<?php declare(strict_types=1);

namespace Olifanton\Ton\Transports\Toncenter\Responses;

use Olifanton\Ton\Marshalling\Attributes\JsonMap;

class Base64Address
{
    #[JsonMap]
    public readonly string $b64;

    #[JsonMap]
    public readonly string $b64url;
}
