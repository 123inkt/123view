<?php

declare(strict_types=1);

namespace DR\Review\Utility;

interface ComparableInterface
{
    /**
     * @phpstan-return -1|0|1
     */
    public function compareTo(mixed $other): int;
}
