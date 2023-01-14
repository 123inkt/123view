<?php
declare(strict_types=1);

namespace DR\Review\Service\Git\Review;

class FileDiffOptions
{
    public const DEFAULT_LINE_DIFF = 9999999;

    public function __construct(public readonly int $unifiedDiffLines)
    {
    }

    public function __toString(): string
    {
        return sprintf('fdo-%s', $this->unifiedDiffLines);
    }
}
