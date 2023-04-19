<?php
declare(strict_types=1);

namespace DR\Review\Utility;

interface EquatableInterface
{
    public function equalsTo(mixed $other): bool;
}
