<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service\Git\Review\ReviewDiffService;

use DR\GitCommitNotification\Entity\Repository\Repository;
use DR\GitCommitNotification\Service\Git\Diff\GitDiffService;
use DR\GitCommitNotification\Service\Git\Review\FileDiffOptions;
use DR\GitCommitNotification\Service\Git\Review\Strategy\ReviewDiffStrategyInterface;
use DR\GitCommitNotification\Utility\Arrays;
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
    public function getDiffFiles(Repository $repository, array $revisions, ?FileDiffOptions $options = null): array
    {
        if (count($revisions) === 0) {
            return $revisions;
        }

        if (count($revisions) === 1) {
            // get the diff for the single revision
            return $this->diffService->getDiffFromRevision(Arrays::first($revisions), $options);
        }

        /** @var ReviewDiffStrategyInterface $strategy */
        foreach ($this->reviewDiffStrategies as $strategy) {
            try {
                return $strategy->getDiffFiles($repository, $revisions, $options);
            } catch (Throwable $exception) {
                $this->logger?->notice($exception->getMessage(), ['exception' => $exception]);
                continue;
            }
        }

        throw new RuntimeException('Failed to fetch diff for revisions. All strategies exhausted');
    }
}
