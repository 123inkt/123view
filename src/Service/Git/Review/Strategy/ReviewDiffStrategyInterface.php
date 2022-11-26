<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service\Git\Review\Strategy;

use DR\GitCommitNotification\Entity\Git\Diff\DiffFile;
use DR\GitCommitNotification\Entity\Repository\Repository;
use DR\GitCommitNotification\Entity\Review\Revision;
use DR\GitCommitNotification\Exception\ParseException;
use DR\GitCommitNotification\Exception\RepositoryException;
use DR\GitCommitNotification\Service\Git\Review\FileDiffOptions;

interface ReviewDiffStrategyInterface
{
    /**
     * @param Revision[] $revisions
     *
     * @return DiffFile[]
     * @throws RepositoryException|ParseException
     */
    public function getDiffFiles(Repository $repository, array $revisions, ?FileDiffOptions $options = null): array;
}
