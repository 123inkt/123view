<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Utility;

use RuntimeException;

class Type
{
    /**
     * Typecast value to not null
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
}
