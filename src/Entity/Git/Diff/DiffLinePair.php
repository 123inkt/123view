<?php
declare(strict_types=1);

namespace DR\Review\Entity\Git\Diff;

class DiffLinePair
{
    public function __construct(public readonly DiffLine $removed, public readonly DiffLine $added)
    {
    }
}
