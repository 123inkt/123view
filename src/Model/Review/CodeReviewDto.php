<?php
declare(strict_types=1);

namespace DR\Review\Model\Review;

use DR\Review\Entity\Git\Diff\DiffComparePolicy;
use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Model\Review\Action\AbstractReviewAction;
use DR\Review\ViewModel\App\Review\ReviewDiffModeEnum;

readonly class CodeReviewDto
{
    /**
     * @codeCoverageIgnore Simple DTO
     *
     * @param CodeReview[]                $similarReviews
     * @param Revision[]                  $revisions
     * @param Revision[]                  $visibleRevisions
     * @param DirectoryTreeNode<DiffFile> $fileTree
     */
    public function __construct(
        public CodeReview $review,
        public array $similarReviews,
        public array $revisions,
        public array $visibleRevisions,
        public DirectoryTreeNode $fileTree,
        public ?DiffFile $selectedFile,
        public ?string $filePath,
        public string $tab,
        public DiffComparePolicy $comparePolicy,
        public ReviewDiffModeEnum $diffMode,
        public ?AbstractReviewAction $action,
        public int $visibleLines,
    ) {
    }
}
