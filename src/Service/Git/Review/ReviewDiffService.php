<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service\Git\Review;

use DR\GitCommitNotification\Entity\Config\Repository;
use DR\GitCommitNotification\Entity\Git\Diff\DiffFile;
use DR\GitCommitNotification\Entity\Review\Revision;
use DR\GitCommitNotification\Exception\ParseException;
use DR\GitCommitNotification\Exception\RepositoryException;
use DR\GitCommitNotification\Service\Git\Diff\GitDiffService;
use DR\GitCommitNotification\Utility\Type;
use Symfony\Contracts\Cache\CacheInterface;
use Throwable;
use function count;

class ReviewDiffService
{
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

        /** @var Revision $revision */
        $revision = Type::notFalse(reset($revisions));
        /** @var Repository $repository */
        $repository = $revision->getRepository();

        $key = sprintf('%s-%s', $repository->getId(), implode('-', $hashes));

        return $this->revisionCache->get($key, fn() => $this->getDiffFilesFromGit($repository, $revision, $revisions));
    }

    /**
     * @param Revision[] $revisions
     *
     * @return DiffFile[]
     * @throws RepositoryException|ParseException
     */
    private function getDiffFilesFromGit(Repository $repository, Revision $revision, array $revisions): array
    {
        if (count($revisions) === 1) {
            // get the diff for the single revision
            return $this->diffService->getDiffFromRevision($revision);
        }



        return $files;
    }
}
