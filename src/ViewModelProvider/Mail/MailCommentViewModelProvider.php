<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\ViewModelProvider\Mail;

use DR\GitCommitNotification\Entity\Review\CodeReview;
use DR\GitCommitNotification\Entity\Review\Comment;
use DR\GitCommitNotification\Entity\Review\CommentReply;
use DR\GitCommitNotification\Entity\Review\LineReference;
use DR\GitCommitNotification\Service\CodeReview\DiffFinder;
use DR\GitCommitNotification\Service\Git\Review\ReviewDiffService;
use DR\GitCommitNotification\Utility\Type;
use DR\GitCommitNotification\ViewModel\Mail\CommentViewModel;
use Throwable;

class MailCommentViewModelProvider
{
    public function __construct(
        private readonly ReviewDiffService $diffService,
        private readonly DiffFinder $diffFinder
    ) {
    }

    /**
     * @throws Throwable
     */
    public function createCommentViewModel(Comment $comment): CommentViewModel
    {
        /** @var CodeReview $review */
        $review = $comment->getReview();
        /** @var LineReference $lineReference */
        $lineReference = $comment->getLineReference();
        $files         = $this->diffService->getDiffFiles($review->getRevisions()->toArray());

        // find selected file
        $selectedFile = $this->diffFinder->findFileByPath($files, $lineReference->filePath);
        $lineRange    = [];
        if ($selectedFile !== null) {
            $lineRange = $this->diffFinder->findLinesAround($selectedFile, Type::notNull($lineReference), 3) ?? [];
        }

        return new CommentViewModel($review, $comment, [], $selectedFile, $lineRange['before'] ?? [], $lineRange['after'] ?? []);
    }

    /**
     * @throws Throwable
     */
    public function createReplyCommentViewModel(CommentReply $reply): CommentViewModel
    {
        /** @var Comment $comment */
        $comment = $reply->getComment();
        /** @var CodeReview $review */
        $review = $comment->getReview();
        /** @var LineReference $lineReference */
        $lineReference = $comment->getLineReference();
        $files         = $this->diffService->getDiffFiles($review->getRevisions()->toArray());

        // find selected file
        $selectedFile = $this->diffFinder->findFileByPath($files, $lineReference->filePath);
        $lineRange    = [];
        if ($selectedFile !== null) {
            $lineRange = $this->diffFinder->findLinesAround($selectedFile, Type::notNull($lineReference), 3) ?? [];
        }

        $replies = [];
        foreach ($comment->getReplies() as $reaction) {
            $replies[] = $reaction;
            if ($reaction === $reply) {
                break;
            }
        }

        return new CommentViewModel($review, $comment, $replies, $selectedFile, $lineRange['before'] ?? [], $lineRange['after'] ?? []);
    }
}
