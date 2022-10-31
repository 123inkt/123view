<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\MessageHandler;

use DR\GitCommitNotification\Doctrine\Type\CommentStateType;
use DR\GitCommitNotification\Entity\Review\NotificationStatus;
use DR\GitCommitNotification\Message\Comment\CommentAdded;
use DR\GitCommitNotification\Message\Comment\CommentReplyAdded;
use DR\GitCommitNotification\Message\Comment\CommentResolved;
use DR\GitCommitNotification\Repository\Config\UserRepository;
use DR\GitCommitNotification\Repository\Review\CommentReplyRepository;
use DR\GitCommitNotification\Repository\Review\CommentRepository;
use DR\GitCommitNotification\Service\Mail\MailService;
use DR\GitCommitNotification\Utility\Type;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;
use Throwable;

#[AsMessageHandler]
class MailNotificationMessageHandler implements MessageSubscriberInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private readonly MailService $mailService,
        private readonly CommentRepository $commentRepository,
        private readonly CommentReplyRepository $replyRepository,
        private readonly UserRepository $userRepository
    ) {
    }

    /**
     * @throws Throwable
     */
    public function handleCommentAdded(CommentAdded $message): void
    {
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

        $this->mailService->sendNewCommentMail(Type::notNull($comment->getReview()), $comment);

        // update status and save
        $comment->getNotificationStatus()->addStatus(NotificationStatus::STATUS_CREATED);
        $this->commentRepository->save($comment);
    }

    /**
     * @throws Throwable
     */
    public function handleCommentReplyAdded(CommentReplyAdded $message): void
    {
        $this->logger?->info('MailNotificationMessageHandler: comment reply: ' . $message->commentReplyId);
    }

    /**
     * @throws Throwable
     */
    public function handleCommentResolved(CommentResolved $message): void
    {
        $this->logger?->info('MailNotificationMessageHandler: comment resolved: ' . $message->commentId);

        $comment = $this->commentRepository->find($message->commentId);
        $user    = $this->userRepository->find($message->resolveByUserId);
        if ($comment === null || $user === null || $comment->getState() !== CommentStateType::RESOLVED) {
            return;
        }

        // a notification was already send for this comment
        if ($comment->getNotificationStatus()->hasStatus(NotificationStatus::STATUS_RESOLVED)) {
            return;
        }

        $this->mailService->sendCommentResolvedMail(Type::notNull($comment->getReview()), $comment, $user);

        // update status and save
        $comment->getNotificationStatus()->addStatus(NotificationStatus::STATUS_RESOLVED);
        $this->commentRepository->save($comment);
    }

    /**
     * @return iterable<string, array<string, string>>
     */
    public static function getHandledMessages(): iterable
    {
        yield CommentAdded::class => ['method' => 'handleCommentAdded', 'from_transport' => 'async_delay_mail'];
        yield CommentReplyAdded::class => ['method' => 'handleCommentReplyAdded', 'from_transport' => 'async_delay_mail'];
        yield CommentResolved::class => ['method' => 'handleCommentResolved', 'from_transport' => 'async_delay_mail'];
    }
}
