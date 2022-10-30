<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Utility;

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
     * @param T   $item
     *
     * @return T[]
     */
    public static function remove(array $items, mixed $item): array
    {
        $index = array_search($item, $items, true);
        if ($index !== false) {
            unset($items[$index]);
        }

        return $items;
    }
}
