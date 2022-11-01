<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\ViewModelProvider\Mail;

use DR\GitCommitNotification\Entity\Config\User;
use DR\GitCommitNotification\Entity\Review\CodeReview;
use DR\GitCommitNotification\Entity\Review\Comment;
use DR\GitCommitNotification\Entity\Review\CommentReply;
use DR\GitCommitNotification\Entity\Review\LineReference;
use DR\GitCommitNotification\Service\CodeReview\DiffFinder;
use DR\GitCommitNotification\Service\Git\Review\ReviewDiffService;
use DR\GitCommitNotification\Utility\Assert;
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
    public function createCommentViewModel(
        CodeReview $review,
        Comment $comment,
        ?CommentReply $reply = null,
        ?User $resolvedBy = null
    ): CommentViewModel {
        /** @var LineReference $lineReference */
        $lineReference = $comment->getLineReference();
        $files         = $this->diffService->getDiffFiles($review->getRevisions()->toArray());

        // find selected file
        $selectedFile = $this->diffFinder->findFileByPath($files, $lineReference->filePath);
        $lineRange    = [];
        if ($selectedFile !== null) {
            $lineRange = $this->diffFinder->findLinesAround($selectedFile, Assert::notNull($lineReference), 4) ?? [];
        }

        $headerTitle = $this->getHeaderTitle($reply, $resolvedBy);

        // gather replies to show
        $replies = $this->getReplies($comment, $reply, $resolvedBy !== null);

        return new CommentViewModel(
            $headerTitle,
            $review,
            $comment,
            $replies,
            $selectedFile,
            $lineRange['before'] ?? [],
            $lineRange['after'] ?? [],
            $resolvedBy
        );
    }

    private function getHeaderTitle(?CommentReply $reply, ?User $resolvedBy): string
    {
        if ($resolvedBy !== null) {
            return 'mail.comment.was.resolved.on';
        }

        if ($reply !== null) {
            return 'mail.new.reply.by.user.on';
        }

        return 'mail.new.comment.by.user.on';
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
