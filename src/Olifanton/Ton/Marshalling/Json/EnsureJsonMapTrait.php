<?php declare(strict_types=1);

namespace Olifanton\Ton\Marshalling\Json;

use Olifanton\Ton\Marshalling\Attributes\JsonMap;
use ReflectionNamedType;

trait EnsureJsonMapTrait
{
    /**
     * @param class-string $messageClazz
     * @return array<string, array>|null
     * @throws \ReflectionException
     */
    protected static function ensureJsonMap(string $messageClazz): ?array
    {
        if (isset(JsonMapAttributeHolder::$mapCache[$messageClazz])) {
            return JsonMapAttributeHolder::$mapCache[$messageClazz];
        }

        $map = [];

        $reflection = new \ReflectionClass($messageClazz);
        $reflectionProperties = $reflection->getProperties(\ReflectionProperty::IS_PUBLIC | \ReflectionProperty::IS_PROTECTED);

        foreach ($reflectionProperties as $reflectionProperty) {
            $defineGetterAttrs = $reflectionProperty->getAttributes(JsonMap::class);

            if (!empty($defineGetterAttrs)) {
                $propertyName = $reflectionProperty->getName();
                $jsonMapAttr = $defineGetterAttrs[0];
                $jsonMapAttrArgs = self::normalizeAttributeArguments($jsonMapAttr->getArguments());
                /** @var ReflectionNamedType|null $propertyType */
                $propertyType = $reflectionProperty->getType();
                $map[$propertyName] = [
                    "result_name" => !empty($jsonMapAttrArgs['propertyName'])
                        ? $jsonMapAttrArgs['propertyName']
                        : $propertyName,
                    "serializer" => $jsonMapAttrArgs['serializer'],
                    "type" => ($propertyType instanceof ReflectionNamedType)
                        ? $propertyType->getName()
                        : null,
                    "type_allows_null" => $propertyType?->allowsNull(),
                    "param0" => $jsonMapAttrArgs['param0'],
                    "param1" => $jsonMapAttrArgs['param1'],
                ];
            }
        }

        if (!empty($map)) {
            JsonMapAttributeHolder::$mapCache[$messageClazz] = $map;

            return $map;
        }

        return null;
    }

    /**
     * @return array<string, mixed>
     */
    private static function normalizeAttributeArguments(array $jsonMapAttrArgs): array
    {
        $args = [
            'propertyName',
            'serializer',
            'param0',
            'param1',
        ];
        $result = array_fill_keys($args, null);
        $result['serializer'] = JsonMap::SER_DEFAULT;

        foreach ($args as $index => $name) {
            $result[$name] = $jsonMapAttrArgs[$name] ?? $jsonMapAttrArgs[$index] ?? $result[$name];
        }

        return $result;
    }
}
