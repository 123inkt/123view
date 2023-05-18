<?php
declare(strict_types=1);

namespace DR\Review\Utility;

use RuntimeException;

class Arrays
{
    /**
     * @template T
     *
     * @param T[] $items
     *
     * @return T
     */
    public static function first(array $items): mixed
    {
        $result = reset($items);
        if ($result === false) {
            throw new RuntimeException('Unable to obtain first item from array');
        }

        return $result;
    }

    /**
     * @template T
     *
     * @param T[] $items
     *
     * @return T|null
     */
    public static function firstOrNull(array $items): mixed
    {
        $result = reset($items);

        return $result === false ? null : $result;
    }

    /**
     * @template T
     *
     * @param T[] $items
     *
     * @return T
     */
    public static function last(array $items): mixed
    {
        $result = end($items);
        if ($result === false) {
            throw new RuntimeException('Unable to obtain last item from array');
        }

        return $result;
    }

    /**
     * @template T
     *
     * @param T[] $items
     *
     * @return T|null
     */
    public static function lastOrNull(array $items): mixed
    {
        $result = end($items);

        return $result === false ? null : $result;
    }

    /**
     * @template T
     *
     * @param T[]              $items
     * @param callable(T):bool $callback
     *
     * @return T|null
     */
    public static function tryFind(array $items, callable $callback): mixed
    {
        foreach ($items as $item) {
            if ($callback($item)) {
                return $item;
            }
        }

        return null;
    }

    /**
     * @template T
     * @template K of int|string
     *
     * @param T[]                              $items
     * @param (callable(T): array{0: K, 1: T}) $callback
     *
     * @return array<K, T>
     */
    public static function mapAssoc(array $items, callable $callback): array
    {
        $result = [];
        foreach ($items as $item) {
            $keyValuePair             = $callback($item);
            $result[$keyValuePair[0]] = $keyValuePair[1];
        }

        return $result;
    }

    /**
     * @template T
     * @template K of int|string
     *
     * @param T[]              $items
     * @param (callable(T): K) $callback
     *
     * @return array<K, T>
     */
    public static function reindex(array $items, callable $callback): array
    {
        $result = [];
        foreach ($items as $item) {
            $result[$callback($item)] = $item;
        }

        return $result;
    }

    /**
     * @template T
     *
     * @param T[] $items
     * @param T   $item
     *
     * @return T[]
     */
    public static function remove(array $items, mixed $item): array
    {
        $index = self::search($items, $item);
        if ($index !== false) {
            unset($items[$index]);
        }

        return $items;
    }

    /**
     * @template T of array-key
     *
     * @param array<T, mixed> $items
     *
     * @phpstan-return T|false
     */
    public static function search(array $items, mixed $needle): int|string|false
    {
        foreach ($items as $key => $value) {
            if ($value === $needle || ($needle instanceof EquatableInterface && $needle->equalsTo($value))) {
                return $key;
            }
        }

        return false;
    }

    /**
     * @template T
     *
     * @param T[] $items
     *
     * @return T[]
     */
    public static function unique(array $items): array
    {
        $result = [];
        foreach ($items as $key => $item) {
            if (self::search($result, $item) === false) {
                $result[$key] = $item;
            }
        }

        return $result;
    }

    /**
     * @template T
     * @template K
     *
     * @param T[] $itemsA
     * @param K[] $itemsB
     *
     * @return T[]
     */
    public static function diff(array $itemsA, array $itemsB): array
    {
        return array_udiff(
            $itemsA,
            $itemsB,
            static function ($itemA, $itemB): int {
                if ($itemA instanceof ComparableInterface) {
                    return $itemA->compareTo($itemB);
                }

                $keyA = is_object($itemA) ? spl_object_hash($itemA) : (string)$itemA;
                $keyB = is_object($itemB) ? spl_object_hash($itemB) : (string)$itemB;

                return strcmp($keyA, $keyB);
            }
        );
    }
}
