<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service\Git\Review;

class FileDiffOptions
{
    public function __construct(public readonly int $unifiedDiffLines)
    {
    }

    public function __toString(): string
    {
        return sprintf('udl:%s', $this->unifiedDiffLines);
    }
}
