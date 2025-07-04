<?php
declare(strict_types=1);

namespace DR\Review\Model\Review;

readonly class RevisionFileChange
{
    /**
     * @codeCoverageIgnore Simple DTO
     */
    public function __construct(public int $revisionId, public int $fileCount, public int $linesAdded, public int $linesRemoved)
    {
    }
}
