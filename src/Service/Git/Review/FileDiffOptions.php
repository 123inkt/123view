<?php
declare(strict_types=1);

namespace DR\Review\Service\Git\Review;

class FileDiffOptions
{
    public function __construct(public readonly int $unifiedDiffLines, public readonly bool $minimal = false)
    {
    }

    public function __toString(): string
    {
        return sprintf('fdo-%s-%s', $this->unifiedDiffLines, $this->minimal);
    }
}
