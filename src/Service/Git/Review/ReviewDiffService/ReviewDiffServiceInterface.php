<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service\Git\Review\ReviewDiffService;

use DR\GitCommitNotification\Entity\Config\Repository;
use DR\GitCommitNotification\Entity\Git\Diff\DiffFile;
use DR\GitCommitNotification\Entity\Review\Revision;
use DR\GitCommitNotification\Service\Git\Review\FileDiffOptions;
use Throwable;

interface ReviewDiffServiceInterface
{
    /**
     * @param Revision[] $revisions
     *
     * @return DiffFile[]
     * @throws Throwable
     */
    public function getDiffFiles(Repository $repository, array $revisions, ?FileDiffOptions $options = null): array;
}
