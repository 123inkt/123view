<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\MessageHandler\Mail;

use DR\GitCommitNotification\Entity\Review\NotificationStatus;
use DR\GitCommitNotification\Message\Comment\CommentReplyAdded;
use DR\GitCommitNotification\Message\MailNotificationInterface;
use DR\GitCommitNotification\Repository\Review\CommentReplyRepository;
use DR\GitCommitNotification\Service\Mail\CommentMailService;
use DR\GitCommitNotification\Utility\Assert;
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
        $this->replyRepository->save($reply);
    }

    public static function accepts(): string
    {
        return CommentReplyAdded::class;
    }
}
