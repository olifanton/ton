<?php declare(strict_types=1);

namespace Olifanton\Ton\Helpers;

/**
 * @phpstan-type Field string
 * @phpstan-type Rule array{0: string, 1: Field|Field[], [2]: Rule|undefined}
 */
final class Validator
{
    public const REQUIRED = "required";
    public const STRING = "string";
    public const INT = "int";
    public const NUM = "num";
    public const FLOAT = "float";
    public const BOOL = "bool";
    public const ARRAY = "array";
    public const RULE = "rule";

    private const KEY_RULE_NAME = 0;
    private const KEY_FIELD = 1;
    private const KEY_INNER_RULE = 2;

    /**
     * @param array<string, mixed> $data
     * @phpstan-param Rule|array ...$rules
     * @param array $rules
     * @return string[]|null
     */
    public static function validate(array $data, array ...$rules): ?array
    {
        /** @phpstan-type array<Field, Rule[]> $ruleMap */
        $ruleMap = [];

        foreach ($rules as $rule) {
            if (!is_array($rule)) {
                throw new \InvalidArgumentException("Invalid rule array");
            }

            if (!isset($rule[self::KEY_RULE_NAME], $rule[self::KEY_FIELD])) {
                throw new \InvalidArgumentException("Invalid rule array");
            }

            $fields = is_array($rule[self::KEY_FIELD]) ? $rule[self::KEY_FIELD] : [$rule[self::KEY_FIELD]];

            foreach ($fields as $field) {
                $ruleMap[$field] = $ruleMap[$field] ?? [];
                $ruleMap[$field][] = $rule;
            }
        }

        $errors = [];

        foreach ($ruleMap as $filed => $rules) {
            foreach ($rules as $rule) {
                if ($result = call_user_func([self::class, "validate" . ucfirst($rule[self::KEY_RULE_NAME])], $filed, $data, $rule)) {
                    $errors[] = $result;
                }
            }
        }

        if (!empty($errors)) {
            return $errors;
        }

        return null;
    }

    protected static function validateRequired(string $field, array $data): ?string
    {
        if (!isset($data[$field])) {
            return "\"$field\" is required";
        }

        return null;
    }

    protected static function validateString(string $field, array $data): ?string
    {
        if (isset($data[$field]) && !is_string($data[$field])) {
            return "\"$field\" is not a string";
        }

        return null;
    }

    protected static function validateInt(string $field, array $data): ?string
    {
        if (isset($data[$field]) && !is_int($data[$field])) {
            return "\"$field\" is not a integer";
        }

        return null;
    }

    protected static function validateNum(string $field, array $data): ?string
    {
        if (isset($data[$field]) && !is_numeric($data[$field])) {
            return "\"$field\" is not a numeric";
        }

        return null;
    }

    protected static function validateBool(string $field, array $data): ?string
    {
        if (isset($data[$field]) && !is_bool($data[$field])) {
            return "\"$field\" is not a boolean";
        }

        return null;
    }

    protected static function validateArray(string $field, array $data): ?string
    {
        if (isset($data[$field]) && !is_array($data[$field])) {
            return "\"$field\" is not an array";
        }

        return null;
    }

    protected static function validateFloat(string $field, array $data): ?string
    {
        if (isset($data[$field]) && !is_float($data[$field])) {
            return "\"$field\" is not a float";
        }

        return null;
    }

    protected static function validateRule(string $field, array $data, array $rule): ?string
    {
        if (isset($data[$field])) {
            if (!is_array($data[$field])) {
                return "\"$field\" is not an array";
            }

            $innerRules = $rule[self::KEY_INNER_RULE] ?? [];
            $innerErrors = self::validate($data[$field], ...$innerRules);

            if ($innerErrors) {
                return "\"$field\" is not an valid array: " . implode(", ", $innerErrors);
            }
        }

        return null;
    }
}
