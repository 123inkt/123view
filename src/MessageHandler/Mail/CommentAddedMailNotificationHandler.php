<?php
declare(strict_types=1);

namespace DR\Review\MessageHandler\Mail;

use DR\Review\Entity\Review\NotificationStatus;
use DR\Review\Message\Comment\CommentAdded;
use DR\Review\Message\MailNotificationInterface;
use DR\Review\Repository\Review\CommentRepository;
use DR\Review\Service\Mail\CommentMailService;
use DR\Utils\Assert;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Throwable;

class CommentAddedMailNotificationHandler implements MailNotificationHandlerInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(private readonly CommentMailService $mailService, private readonly CommentRepository $commentRepository)
    {
    }

    /**
     * @throws Throwable
     */
    public function handle(MailNotificationInterface $message): void
    {
        assert($message instanceof CommentAdded);
        $this->logger?->info('MailNotificationMessageHandler: comment added: ' . $message->commentId);

        $comment = $this->commentRepository->find($message->commentId);
        if ($comment === null) {
            // comment was removed before we could send it
            return;
        }

        // a notification was already send for this comment
        if ($comment->getNotificationStatus()->hasStatus(NotificationStatus::STATUS_CREATED)) {
            return;
        }

        $this->mailService->sendNewCommentMail(Assert::notNull($comment->getReview()), $comment);

        // update status and save
        $comment->getNotificationStatus()->addStatus(NotificationStatus::STATUS_CREATED);
        $this->commentRepository->save($comment, true);
    }

    public static function accepts(): string
    {
        return CommentAdded::class;
    }
}
