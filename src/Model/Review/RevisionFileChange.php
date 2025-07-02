<?php
declare(strict_types=1);

namespace DR\Review\Model\Review;

readonly class RevisionFileChange
{
    public function __construct(public int $revisionId, public int $fileCount, public int $linesAdded, public int $linesRemoved)
    {
    }
}
