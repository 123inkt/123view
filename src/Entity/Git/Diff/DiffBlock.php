<?php
declare(strict_types=1);

namespace DR\Review\Entity\Git\Diff;

class DiffBlock
{
    /** @var array<int, DiffLine> */
    public array $lines = [];
}
