<?php declare(strict_types=1);

namespace Olifanton\Ton\Marshalling\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class JsonMap
{
    public const SER_DEFAULT = 'default';
    public const SER_BIGINT = 'BigInt';
    public const SER_CELL = 'Cell';
    public const SER_TYPE = 'type';

    public function __construct(
        private readonly ?string $propertyName = null,
        private readonly string $serializer = self::SER_DEFAULT,
        private readonly mixed $param0 = null,
        private readonly mixed $param1 = null,
    )
    {
    }

    public function getPropertyName(): ?string
    {
        return $this->propertyName;
    }

    public function getSerializer(): string
    {
        return $this->serializer;
    }

    public function getParam0(): mixed
    {
        return $this->param0;
    }

    public function getParam1(): mixed
    {
        return $this->param1;
    }
}
