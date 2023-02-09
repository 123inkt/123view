<?php
declare(strict_types=1);

namespace DR\Review\Utility;

use RuntimeException;

class Assert
{
    /**
     * Assert value is not null
     * @template T
     *
     * @param T|null $value
     *
     * @return T
     */
    public static function notNull(mixed $value): mixed
    {
        if ($value === null) {
            throw new RuntimeException('Expecting value to be not null');
        }

        return $value;
    }

    /**
     * Assert value is array
     * @template       T
     * @phpstan-assert array $value
     *
     * @param T $value
     *
     * @return T&array
     */
    public static function isArray(mixed $value): array
    {
        if (is_array($value) === false) {
            throw new RuntimeException('Expecting value to be an array');
        }

        return $value;
    }

    /**
     * Assert value is int
     * @template       T
     * @phpstan-assert int $value
     *
     * @param T $value
     *
     * @return T&int
     */
    public static function isInt(mixed $value): int
    {
        if (is_int($value) === false) {
            throw new RuntimeException('Expecting value to be an int');
        }

        return $value;
    }

    /**
     * Assert value is a string
     * @template       T
     * @phpstan-assert string $value
     *
     * @param T $value
     *
     * @return T&string
     */
    public static function isString(mixed $value): string
    {
        if (is_string($value) === false) {
            throw new RuntimeException('Expecting value to be a string');
        }

        return $value;
    }

    /**
     * Assert value is not false
     * @template T
     *
     * @param T|false $value
     *
     * @return T
     */
    public static function notFalse(mixed $value): mixed
    {
        if ($value === false) {
            throw new RuntimeException('Expecting value to be not false');
        }

        return $value;
    }

    /**
     * Assert value is object and of type class-string
     * @template T
     *
     * @param class-string<T> $classString
     *
     * @return T
     */
    public static function instanceOf(string $classString, mixed $value): object
    {
        if (is_subclass_of($value, $classString) === false) {
            throw new RuntimeException('Expecting value to be instance of ' . $classString);
        }

        return $value;
    }
}
