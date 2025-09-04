<?php
declare(strict_types=1);

namespace DR\Review\Service\Git\Review;

use DR\Review\Doctrine\Type\CodeReviewType;
use DR\Review\Entity\Git\Diff\DiffComparePolicy;

class FileDiffOptions
{
    public const DEFAULT_LINE_DIFF = 9999999;

    /**
     * @phpstan-param CodeReviewType::COMMITS|CodeReviewType::BRANCH|null $reviewType
     */
    public function __construct(
        public readonly int $unifiedDiffLines,
        public readonly DiffComparePolicy $comparePolicy,
        public readonly ?string $reviewType = null,
        public readonly ?int $visibleLines = null,
    ) {
    }

    public function __toString(): string
    {
        return sprintf(
            'fdo-%s-%s-%s-%d',
            $this->unifiedDiffLines,
            $this->comparePolicy->value,
            $this->reviewType ?? CodeReviewType::COMMITS,
            $this->visibleLines ?? -1
        );
    }
}
