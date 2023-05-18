<?php
declare(strict_types=1);

namespace DR\Review\Service\Git\Review\ReviewDiffService;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Service\Git\Review\FileDiffOptions;
use Symfony\Contracts\Cache\CacheInterface;

class CacheableReviewDiffService implements ReviewDiffServiceInterface
{
    public function __construct(private readonly CacheInterface $revisionCache, private readonly ReviewDiffServiceInterface $diffService)
    {
    }

    /**
     * @inheritDoc
     */
    public function getDiffForRevisions(Repository $repository, array $revisions, ?FileDiffOptions $options = null): array
    {
        if (count($revisions) === 0) {
            return [];
        }

        // gather hashes
        $hashes = array_map(static fn(Revision $revision) => $revision->getCommitHash(), $revisions);

        $key = sprintf('diff-files-revision-%s-%s-%s', $repository->getId(), implode('-', $hashes), $options);

        return $this->revisionCache->get($key, fn() => $this->diffService->getDiffForRevisions($repository, $revisions, $options));
    }

    /**
     * @inheritDoc
     */
    public function getDiffForBranch(Repository $repository, string $branchName, ?FileDiffOptions $options = null): array
    {
        $key = sprintf('diff-files-branch %s-%s-%s', $repository->getId(), $branchName, $options);

        return $this->revisionCache->get($key, fn() => $this->diffService->getDiffForBranch($repository, $branchName, $options));
    }
}
