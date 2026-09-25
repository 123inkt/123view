<?php
declare(strict_types=1);

namespace DR\Review\Service\Git\Review\ReviewDiffService;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Service\Git\Diff\GitDiffService;
use DR\Review\Service\Git\Review\FileDiffOptions;
use DR\Review\Service\Git\Review\Strategy\ReviewDiffStrategyInterface;
use DR\Utils\Arrays;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use RuntimeException;
use Throwable;
use Traversable;
use function count;

class ReviewDiffService implements LoggerAwareInterface, ReviewDiffServiceInterface
{
    use LoggerAwareTrait;

    /**
     * @param Traversable<ReviewDiffStrategyInterface> $reviewDiffStrategies
     */
    public function __construct(private readonly GitDiffService $diffService, private readonly Traversable $reviewDiffStrategies)
    {
    }

    /**
     * @inheritDoc
     */
    public function getDiffForRevisions(Repository $repository, array $revisions, ?FileDiffOptions $options = null): array
    {
        if (count($revisions) === 0) {
            return $revisions;
        }

        if (count($revisions) === 1) {
            // get the diff for the single revision
            return $this->diffService->getDiffFromRevision(Arrays::first($revisions), $options);
        }

        $finalException = null;

        /** @var ReviewDiffStrategyInterface $strategy */
        foreach ($this->reviewDiffStrategies as $strategy) {
            try {
                return $strategy->getDiffFiles($repository, $revisions, $options);
            } catch (Throwable $exception) {
                $this->logger?->notice($exception->getMessage(), ['exception' => $exception]);
                $finalException = $exception;
                continue;
            }
        }

        throw new RuntimeException(
            'Failed to fetch diff for revisions. All strategies exhausted. Final error: ' . ($finalException?->getMessage() ?? 'unknown'),
            0,
            $finalException
        );
    }

    /**
     * @inheritDoc
     */
    public function getDiffForBranch(CodeReview $review, array $revisions, string $branchName, ?FileDiffOptions $options = null): array
    {
        return $this->diffService->getBundledDiffFromBranch($review->getRepository(), $branchName, 'origin/' . $review->getTargetBranch(), $options);
    }
}
