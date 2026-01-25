<?php
declare(strict_types=1);

namespace DR\Review\ViewModelProvider;

use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Repository\Review\CommentRepository;
use DR\Review\Service\CodeReview\DiffFinder;
use DR\Review\Service\CodeReview\UserReviewSettingsProvider;
use DR\Review\ViewModel\App\Comment\CommentsViewModel;
use DR\Utils\Arrays;
use DR\Utils\Assert;

class CommentsViewModelProvider
{
    public function __construct(
        private readonly CommentRepository $commentRepository,
        private readonly DiffFinder $diffFinder,
        private readonly UserReviewSettingsProvider $settingsProvider,
    ) {
    }

    public function getCommentsViewModel(CodeReview $review, ?DiffFile $fileBefore, DiffFile $file): CommentsViewModel
    {
        $comments         = $this->commentRepository->findByReview($review, Arrays::removeNull([$file->filePathAfter, $file->filePathBefore]));
        $detachedComments = [];
        $groupedComments  = [];

        // 1) find the DiffLine for the given LineReference
        // 2) if line exists, assign to grouped comments
        // 3) if not, add to detached comments
        foreach ($comments as $comment) {
            $lineReference = Assert::notNull($comment->getLineReference());

            if ($fileBefore === null || $lineReference->lineAfter !== 0) {
                $line = $this->diffFinder->findLineInFile($file, $lineReference);
            } else {
                $line = $this->diffFinder->findLineInFile($fileBefore, $lineReference);
            }

            if ($line !== null) {
                $groupedComments[spl_object_hash($line)][] = $comment;
            } else {
                $detachedComments[] = $comment;
            }
        }

        return new CommentsViewModel(
            $groupedComments,
            $detachedComments,
            $this->settingsProvider->getComparisonPolicy(),
            $this->settingsProvider->getCommentVisibility()
        );
    }
}
