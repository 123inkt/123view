<?php
declare(strict_types=1);

namespace DR\Review\Service\Git\Review\ReviewDiffService;

use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Review\Revision;
use DR\Review\Service\Git\Review\FileDiffOptions;
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
