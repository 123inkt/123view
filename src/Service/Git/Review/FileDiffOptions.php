<?php
declare(strict_types=1);

namespace DR\Review\Service\Git\Review;

use DR\Review\Entity\Git\Diff\DiffComparePolicy;

class FileDiffOptions
{
    public const DEFAULT_LINE_DIFF = 9999999;

    public function __construct(public readonly int $unifiedDiffLines, public readonly DiffComparePolicy $comparePolicy)
    {
    }

    public function __toString(): string
    {
        return sprintf('fdo-%s-%s', $this->unifiedDiffLines, $this->comparePolicy->value);
    }
}
