<?php
declare(strict_types=1);

namespace DR\Review\Service\Git\Review\ReviewDiffService;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Service\Git\Diff\GitDiffService;
use DR\Review\Service\Git\Review\FileDiffOptions;
use DR\Review\Service\Git\Review\Strategy\ReviewDiffStrategyInterface;
use DR\Review\Utility\Arrays;
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

        $files = null;
        if (count($revisions) === 1) {
            // get the diff for the single revision
            $files = $this->diffService->getDiffFromRevision(Arrays::first($revisions), $options);
        } else {
            /** @var ReviewDiffStrategyInterface $strategy */
            foreach ($this->reviewDiffStrategies as $strategy) {
                try {
                    $files = $strategy->getDiffFiles($repository, $revisions, $options);
                    break;
                } catch (Throwable $exception) {
                    $this->logger?->notice($exception->getMessage(), ['exception' => $exception]);
                }
            }
        }

        if ($files === null) {
            throw new RuntimeException('Failed to fetch diff for revisions. All strategies exhausted');
        }

        if ($options?->minimal === true) {
            foreach ($files as $file) {
                $file->getNrOfLinesAdded();
                $file->getNrOfLinesRemoved();
                $file->removeBlocks();
            }
        }

        return $files;
    }
}
