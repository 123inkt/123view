<?php
declare(strict_types=1);

namespace DR\Review\Service\CodeReview;

use DR\Review\Doctrine\Type\CodeReviewType;
use DR\Review\Entity\Git\Diff\DiffComparePolicy;
use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Service\Git\Review\FileDiffOptions;
use DR\Review\Service\Git\Review\ReviewDiffService\ReviewDiffServiceInterface;
use Throwable;

readonly class CodeReviewDiffService
{
    public function __construct(private ReviewDiffServiceInterface $diffService, private CodeReviewRevisionService $revisionService)
    {
    }

    /**
     * @return DiffFile[]
     * @throws Throwable
     */
    public function getDiff(CodeReview $review): array
    {
        $options = new FileDiffOptions(5, DiffComparePolicy::IGNORE_EMPTY_LINES, includeRaw: true);

        // gather files for review revisions
        if ($review->getType() === CodeReviewType::BRANCH) {
            return $this->diffService->getDiffForBranch($review, [], (string)$review->getReferenceId(), $options);
        }

        return $this->diffService->getDiffForRevisions($review->getRepository(), $this->revisionService->getRevisions($review), $options);
    }
}
