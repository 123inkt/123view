<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service\Git\Review;

use DR\GitCommitNotification\Entity\Config\Repository;
use DR\GitCommitNotification\Entity\Git\Diff\DiffFile;
use DR\GitCommitNotification\Entity\Review\Revision;
use DR\GitCommitNotification\Exception\ParseException;
use DR\GitCommitNotification\Exception\RepositoryException;
use DR\GitCommitNotification\Service\Git\Diff\GitDiffService;
use DR\GitCommitNotification\Service\Git\Review\Strategy\ReviewDiffStrategyInterface;
use DR\GitCommitNotification\Utility\Arrays;
use DR\GitCommitNotification\Utility\Type;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use RuntimeException;
use Symfony\Contracts\Cache\CacheInterface;
use Throwable;
use Traversable;
use function count;

class ReviewDiffService implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @param Traversable<ReviewDiffStrategyInterface> $reviewDiffStrategies
     */
    public function __construct(
        private readonly GitDiffService $diffService,
        private readonly CacheInterface $revisionCache,
        private readonly Traversable $reviewDiffStrategies,
    ) {
    }

    /**
     * @param Revision[] $revisions
     *
     * @return DiffFile[]
     * @throws Throwable
     */
    public function getDiffFiles(array $revisions): array
    {
        if (count($revisions) === 0) {
            return [];
        }

        // gather hashes
        $hashes = array_map(static fn(Revision $revision) => $revision->getCommitHash(), $revisions);

        // obtain the repository from the first revision
        $repository = Type::notNull(Arrays::first($revisions)->getRepository());

        $key = sprintf('%s-%s', $repository->getId(), implode('-', $hashes));

        return $this->revisionCache->get($key, fn() => $this->getDiffFilesFromGit($repository, $revisions));
    }

    /**
     * @param Revision[] $revisions
     *
     * @return DiffFile[]
     * @throws RepositoryException|ParseException
     */
    private function getDiffFilesFromGit(Repository $repository, array $revisions): array
    {
        if (count($revisions) === 1) {
            // get the diff for the single revision
            return $this->diffService->getDiffFromRevision(Arrays::first($revisions));
        }

        /** @var ReviewDiffStrategyInterface $strategy */
        foreach ($this->reviewDiffStrategies as $strategy) {
            try {
                return $strategy->getDiffFiles($repository, $revisions);
            } catch (Throwable $exception) {
                $this->logger?->notice($exception->getMessage(), ['exception' => $exception]);
                continue;
            }
        }

        throw new RuntimeException('Failed to fetch diff for revisions. All strategies exhausted');
    }
}
