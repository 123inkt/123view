<?php
declare(strict_types=1);

namespace DR\Review\Service\Git\Review;

class FileDiffOptions
{
    public function __construct(public readonly int $unifiedDiffLines)
    {
    }

    public function __toString(): string
    {
        return sprintf('udl-%s', $this->unifiedDiffLines);
    }
}
