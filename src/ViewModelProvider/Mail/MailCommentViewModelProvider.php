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
    public function __construct(private readonly ReviewDiffService $diffService, private readonly DiffFinder $diffFinder)
    {
    }

    /**
     * @throws Throwable
     */
    public function createCommentViewModel(CodeReview $review, Comment $comment, ?CommentReply $reply, bool $resolved): CommentViewModel
    {
        /** @var LineReference $lineReference */
        $lineReference = $comment->getLineReference();
        $files         = $this->diffService->getDiffFiles($review->getRevisions()->toArray());

        // find selected file
        $selectedFile = $this->diffFinder->findFileByPath($files, $lineReference->filePath);
        $lineRange    = [];
        if ($selectedFile !== null) {
            $lineRange = $this->diffFinder->findLinesAround($selectedFile, Type::notNull($lineReference), 4) ?? [];
        }

        // gather replies to show
        $replies = $this->getReplies($comment, $reply, $resolved);

        return new CommentViewModel($review, $comment, $replies, $selectedFile, $lineRange['before'] ?? [], $lineRange['after'] ?? [], $resolved);
    }

    /**
     * @return CommentReply[]
     */
    private function getReplies(Comment $comment, ?CommentReply $reply, bool $resolved): array
    {
        $replies = [];
        if ($resolved || $reply !== null) {
            foreach ($comment->getReplies() as $reaction) {
                $replies[] = $reaction;
                if ($reaction === $reply) {
                    break;
                }
            }
        }

        return $replies;
    }
}
