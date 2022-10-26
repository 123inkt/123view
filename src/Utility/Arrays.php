<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Utility;

use InvalidArgumentException;
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
        if (count($items) === 0) {
            throw new InvalidArgumentException('Provided empty array');
        }

        $result = reset($items);
        if ($result === false) {
            throw new RuntimeException('Unable to obtain first item from array');
        }

        return $result;
    }
}
