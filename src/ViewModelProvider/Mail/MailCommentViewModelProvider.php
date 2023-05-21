<?php
declare(strict_types=1);

namespace DR\Review\ViewModelProvider\Mail;

use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\CommentReply;
use DR\Review\Entity\Review\LineReference;
use DR\Review\Entity\User\User;
use DR\Review\Service\CodeReview\CodeReviewRevisionService;
use DR\Review\Service\CodeReview\DiffFinder;
use DR\Review\Service\Git\Review\ReviewDiffService\ReviewDiffServiceInterface;
use DR\Review\Utility\Assert;
use DR\Review\ViewModel\Mail\CommentViewModel;
use Symfony\Contracts\Translation\TranslatorInterface;
use Throwable;

class MailCommentViewModelProvider
{
    public function __construct(
        private readonly ReviewDiffServiceInterface $diffService,
        private readonly CodeReviewRevisionService $revisionService,
        private readonly DiffFinder $diffFinder,
        private readonly TranslatorInterface $translator
    ) {
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
        $revisions     = $this->revisionService->getRevisions($review);
        $files         = $this->diffService->getDiffForRevisions(Assert::notNull($review->getRepository()), $revisions);

        // find selected file
        $selectedFile = $this->diffFinder->findFileByPath($files, $lineReference->filePath);
        $lineRange    = [];
        if ($selectedFile !== null) {
            $lineRange = $this->diffFinder->findLinesAround($selectedFile, Assert::notNull($lineReference), 6) ?? [];
        }

        $headerTitle = $this->getHeaderTitle($comment, $reply, $resolvedBy);

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

    private function getHeaderTitle(Comment $comment, ?CommentReply $reply, ?User $resolvedBy): string
    {
        if ($resolvedBy !== null) {
            return $this->translator->trans('mail.comment.was.resolved.on', ['userName' => $resolvedBy->getName()]);
        }

        if ($reply !== null) {
            return $this->translator->trans('mail.new.reply.by.user.on', ['userName' => $reply->getUser()?->getName()]);
        }

        return $this->translator->trans('mail.new.comment.by.user.on', ['userName' => $comment->getUser()?->getName()]);
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
