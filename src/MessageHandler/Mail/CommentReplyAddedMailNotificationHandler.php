<?php
declare(strict_types=1);

namespace DR\Review\MessageHandler\Mail;

use DR\Review\Entity\Review\NotificationStatus;
use DR\Review\Message\Comment\CommentReplyAdded;
use DR\Review\Message\MailNotificationInterface;
use DR\Review\Repository\Review\CommentReplyRepository;
use DR\Review\Service\Mail\CommentMailService;
use DR\Utils\Assert;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Throwable;

class CommentReplyAddedMailNotificationHandler implements MailNotificationHandlerInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(private readonly CommentMailService $mailService, private readonly CommentReplyRepository $replyRepository)
    {
    }

    /**
     * @throws Throwable
     */
    public function handle(MailNotificationInterface $message): void
    {
        assert($message instanceof CommentReplyAdded);
        $this->logger?->info('MailNotificationMessageHandler: comment reply: ' . $message->commentReplyId);

        $reply = $this->replyRepository->find($message->commentReplyId);
        if ($reply === null) {
            return;
        }

        // a notification was already send for this comment
        if ($reply->getNotificationStatus()->hasStatus(NotificationStatus::STATUS_CREATED)) {
            return;
        }

        $comment = Assert::notNull($reply->getComment());
        $review  = Assert::notNull($comment->getReview());

        $this->mailService->sendNewCommentReplyMail($review, $comment, $reply);

        // update status and save
        $reply->getNotificationStatus()->addStatus(NotificationStatus::STATUS_CREATED);
        $this->replyRepository->save($reply, true);
    }

    public static function accepts(): string
    {
        return CommentReplyAdded::class;
    }
}
