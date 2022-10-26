<?php declare(strict_types=1);

namespace Olifanton\Ton\Marshalling\Json;

use Brick\Math\BigInteger;
use Olifanton\Boc\Cell;
use Olifanton\Ton\Marshalling\Attributes\JsonMap;
use Olifanton\Ton\Marshalling\Exceptions\MarshallingException;
use Olifanton\Utils\Bytes;

class Hydrator
{
    use EnsureJsonMapTrait;

    /**
     * @template T
     * @param class-string<T> $objClazz
     * @param array|string $data
     * @return T
     * @throws MarshallingException
     */
    public static function extract(string $objClazz, array|string $data): object
    {
        if (is_string($data)) {
            try {
                $data = json_decode($data, true, 512, JSON_THROW_ON_ERROR);
            } catch (\JsonException $e) {
                throw new MarshallingException($e->getMessage(), $e->getCode(), $e);
            }
        }

        try {
            $reflectionClass = new \ReflectionClass($objClazz);
        } catch (\ReflectionException $e) {
            throw new MarshallingException($e->getMessage(), $e->getCode(), $e);
        }

        /** @var T $instance */
        try {
            $instance = $reflectionClass->newInstanceWithoutConstructor();
        } catch (\ReflectionException $e) {
            throw new MarshallingException($e->getMessage(), $e->getCode(), $e);
        }

        try {
            $jsonMap = self::ensureJsonMap($objClazz);
        } catch (\ReflectionException $e) {
            throw new MarshallingException($e->getMessage(), $e->getCode(), $e);
        }

        if (empty($jsonMap)) {
            throw new MarshallingException("Empty Json map for class " . $objClazz);
        }

        $proxySetter = function (string $propertyName, mixed $propertyValue) use ($instance) {
            $instance->{$propertyName} = $propertyValue;
        };

        foreach ($jsonMap as $propertyName => $mapParams) {
            /** @var string $jsonPropertyName */
            $jsonPropertyName = $mapParams["result_name"];
            /** @var string $serializer */
            $serializer = $mapParams["serializer"];
            /** @var string|null $phpType */
            $phpType = $mapParams['type'];
            /** @var bool|null $typeAllowsNull */
            $typeAllowsNull = $mapParams["type_allows_null"];
            $param0 = $mapParams["param0"];
            $param1 = $mapParams["param0"];

            try {
                $jsonValue = self::getJsonValue($data, $jsonPropertyName);
                $val = match ($serializer) {
                    JsonMap::SER_DEFAULT => self::deserializeDefault($jsonValue, $phpType, $typeAllowsNull),
                    JsonMap::SER_BIGINT => self::deserializeBigInt($jsonValue, $typeAllowsNull),
                    JsonMap::SER_CELL => self::deserializeCell($jsonValue, $typeAllowsNull),
                    JsonMap::SER_TYPE => self::deserializeType($jsonValue, $param0, $typeAllowsNull),
                    JsonMap::SER_ARR_OF => self::deserializeArrayOfType($jsonValue, $param0),
                    JsonMap::SER_ENUM => self::deserializeEnum($jsonValue, $param0, $typeAllowsNull),
                    default => throw new \InvalidArgumentException("Unknown serializer type: " . $propertyName),
                };
                $proxySetter->call($instance, $propertyName, $val);
            } catch (\Throwable $e) {
                throw new MarshallingException(
                    "Property \"$propertyName\" deserialization error: " . $e->getMessage() . "; target class: " . $objClazz,
                    $e->getCode(),
                    $e,
                );
            }
        }

        return $instance;
    }

    private static function deserializeDefault(mixed $jsonValue, ?string $phpType, ?bool $typeAllowsNull): mixed
    {
        if ($phpType === 'DateTimeInterface') {
            if (!$jsonValue && $typeAllowsNull) {
                return null;
            }

            return \DateTimeImmutable::createFromFormat(DATE_ATOM, $jsonValue);
        }

        // @TODO: Deserialize PHP types

        return $jsonValue;
    }

    private static function deserializeBigInt(mixed $jsonValue,  ?bool $typeAllowsNull): ?BigInteger
    {
        if ($jsonValue === "" || $jsonValue === 0) {
            return BigInteger::zero();
        }

        if (!$jsonValue && $typeAllowsNull) {
            return null;
        }

        return BigInteger::fromBase((string)$jsonValue, 10);
    }

    /**
     * @throws \Olifanton\Boc\Exceptions\CellException
     */
    private static function deserializeCell(mixed $jsonValue, ?bool $typeAllowsNull): ?Cell
    {
        if ($jsonValue === "" || $jsonValue === null && $typeAllowsNull) {
            return null;
        }

        return Cell::oneFromBoc(Bytes::base64ToBytes($jsonValue));
    }

    /**
     * @throws \Throwable
     */
    private static function deserializeType(mixed $jsonValue,
                                            string $typeClazz,
                                            ?bool $typeAllowsNull): mixed
    {
        if (!$jsonValue && $typeAllowsNull) {
            return null;
        }

        return self::extract($typeClazz, $jsonValue);
    }

    /**
     * @throws \Throwable
     */
    private static function deserializeArrayOfType(array $jsonValue, string $typeClazz): array
    {
        $result = [];

        foreach ($jsonValue as $item) {
            $result[] = self::deserializeType($item, $typeClazz, false);
        }

        return $result;
    }

    /**
     * @throws \Throwable
     */
    private static function deserializeEnum(string | int | null $jsonValue, string $enumClazz, ?bool $typeAllowsNull): mixed
    {
        if ($jsonValue === null && $typeAllowsNull) {
            return null;
        }

        return call_user_func_array([$enumClazz, "from"], [$jsonValue]);
    }

    private static function getJsonValue(array $data, string $jsonPropertyName): mixed
    {
        if (str_contains($jsonPropertyName, ".")) {
            $path = explode(".", $jsonPropertyName);
            $prevValue = $data;

            foreach ($path as $key) {
                if (is_array($prevValue) && isset($prevValue[$key])) {
                    $prevValue = $prevValue[$key];
                } else {
                    return null;
                }
            }

            return $prevValue;
        } else {
            if (isset($data[$jsonPropertyName])) {
                return $data[$jsonPropertyName];
            }
        }

        return null;
    }
}
