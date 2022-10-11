<?php declare(strict_types=1);

namespace Olifanton\Ton\Toncenter\Responses;

use Olifanton\Ton\Marshalling\Attributes\JsonMap;

class AddressDetectionResult
{
    #[JsonMap("raw_form")]
    public readonly string $rawForm;

    #[JsonMap(serializer: JsonMap::SER_TYPE, param0: Base64Address::class)]
    public readonly Base64Address $bounceable;

    #[JsonMap("non_bounceable", JsonMap::SER_TYPE, param0: Base64Address::class)]
    public readonly Base64Address $nonBounceable;

    #[JsonMap("given_type")]
    public readonly string $givenType;

    #[JsonMap("test_only")]
    public readonly bool $testOnly;
}
