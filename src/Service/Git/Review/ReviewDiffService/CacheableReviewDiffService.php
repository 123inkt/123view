<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service\Git\Review\ReviewDiffService;

use DR\GitCommitNotification\Entity\Config\Repository;
use DR\GitCommitNotification\Entity\Review\Revision;
use DR\GitCommitNotification\Service\Git\Review\FileDiffOptions;
use Symfony\Contracts\Cache\CacheInterface;

class CacheableReviewDiffService implements ReviewDiffServiceInterface
{
    public function __construct(private readonly CacheInterface $revisionCache, private readonly ReviewDiffServiceInterface $diffService)
    {
    }

    /**
     * @inheritDoc
     */
    public function getDiffFiles(Repository $repository, array $revisions, ?FileDiffOptions $options = null): array
    {
        if (count($revisions) === 0) {
            return [];
        }

        // gather hashes
        $hashes = array_map(static fn(Revision $revision) => $revision->getCommitHash(), $revisions);

        $key = sprintf('diff-files:%s-%s-%s', $repository->getId(), implode('-', $hashes), $options);

        return $this->revisionCache->get($key, fn() => $this->diffService->getDiffFiles($repository, $revisions, $options));
    }
}
