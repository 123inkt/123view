<?php
declare(strict_types=1);

namespace DR\Review\MessageHandler\Mail;

use DR\Review\Message\Comment\CommentReplyUpdated;
use DR\Review\Message\MailNotificationInterface;
use DR\Review\Repository\Review\CommentReplyRepository;
use DR\Review\Service\CodeReview\Comment\CommentMentionService;
use DR\Review\Service\Mail\CommentMailService;
use DR\Review\Utility\Arrays;
use DR\Utils\Assert;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Throwable;

class CommentReplyUpdatedMailNotificationHandler implements MailNotificationHandlerInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private readonly CommentMailService $mailService,
        private readonly CommentReplyRepository $replyRepository,
        private readonly CommentMentionService $mentionService
    ) {
    }

    /**
     * @throws Throwable
     */
    public function handle(MailNotificationInterface $message): void
    {
        assert($message instanceof CommentReplyUpdated);
        $this->logger?->info('MailNotificationMessageHandler: comment reply updated: ' . $message->commentReplyId);

        $reply = $this->replyRepository->find($message->commentReplyId);
        if ($reply === null) {
            // comment was removed before we could send it
            return;
        }

        $mentions = array_values($this->mentionService->getMentionedUsers((string)$reply->getMessage()));
        if (count($mentions) === 0) {
            return;
        }

        $originalMentions = $this->mentionService->getMentionedUsers($message->originalComment);
        $newMentions      = Arrays::unique(Arrays::diff($mentions, $originalMentions));

        if (count($newMentions) === 0) {
            return;
        }

        $comment = Assert::notNull($reply->getComment());
        $review  = Assert::notNull($comment->getReview());

        $this->logger?->info('MailNotificationMessageHandler: sending new mentions to comment reply');

        $this->mailService->sendNewCommentReplyMail($review, $comment, $reply, $newMentions);
    }

    public static function accepts(): string
    {
        return CommentReplyUpdated::class;
    }
}
