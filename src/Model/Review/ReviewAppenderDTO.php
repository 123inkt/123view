<?php
declare(strict_types=1);

namespace DR\Review\Model\Review;

use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Revision\Revision;

readonly class ReviewAppenderDTO
{
    /**
     * @param Revision[]                  $revisions
     * @param DirectoryTreeNode<DiffFile> $fileTree
     */
    public function __construct(
        public CodeReview $review,
        public array $revisions,
        public DirectoryTreeNode $fileTree,
        public ?DiffFile $selectedFile
    ) {
    }
}
