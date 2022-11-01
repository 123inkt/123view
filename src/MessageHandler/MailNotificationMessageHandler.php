<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\MessageHandler;

use DR\GitCommitNotification\Doctrine\Type\CommentStateType;
use DR\GitCommitNotification\Entity\Review\NotificationStatus;
use DR\GitCommitNotification\Message\Comment\CommentAdded;
use DR\GitCommitNotification\Message\Comment\CommentReplyAdded;
use DR\GitCommitNotification\Message\Comment\CommentResolved;
use DR\GitCommitNotification\Message\Delay\DelayableMessage;
use DR\GitCommitNotification\Message\MailNotificationInterface;
use DR\GitCommitNotification\Repository\Config\UserRepository;
use DR\GitCommitNotification\Repository\Review\CommentReplyRepository;
use DR\GitCommitNotification\Repository\Review\CommentRepository;
use DR\GitCommitNotification\Service\Mail\MailService;
use DR\GitCommitNotification\Utility\Type;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;
use Throwable;

#[AsMessageHandler]
class MailNotificationMessageHandler implements MessageSubscriberInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private readonly MailService $mailService,
        private readonly CommentRepository $commentRepository,
        private readonly CommentReplyRepository $replyRepository,
        private readonly UserRepository $userRepository,
        private readonly MessageBusInterface $bus
    ) {
    }

    /**
     * Stage 1: any mail notification message should be resubmitted with a 600 seconds delay
     * @throws Throwable
     */
    public function delayMessage(MailNotificationInterface $message): void
    {
        $this->logger?->info('MailNotificationMessageHandler: delay message for 600 seconds: ' . get_class($message));

        $this->bus->dispatch(new Envelope(new DelayableMessage($message), [new DelayStamp(600000)]));
    }

    /**
     * Stage 2: a delayed mail notification message was received, dispatch to appropriate handler.
     * @throws Throwable
     */
    public function handleDelayedMessage(DelayableMessage $message): void
    {
        $this->logger?->info('MailNotificationMessageHandler: delayed message received: ' . get_class($message->message));

        if ($message->message instanceof CommentAdded) {
            $this->handleCommentAdded($message->message);
        } elseif ($message->message instanceof CommentReplyAdded) {
            $this->handleCommentReplyAdded($message->message);
        } elseif ($message->message instanceof CommentResolved) {
            $this->handleCommentResolved($message->message);
        }
    }

    /**
     * @return iterable<string, array<string, string>>
     */
    public static function getHandledMessages(): iterable
    {
        yield MailNotificationInterface::class => ['method' => 'delayMessage', 'from_transport' => 'async_messages'];
        yield DelayableMessage::class => ['method' => 'handleDelayedMessage', 'from_transport' => 'async_delay_mail'];
    }

    /**
     * @throws Throwable
     */
    private function handleCommentAdded(CommentAdded $message): void
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
    private function handleCommentReplyAdded(CommentReplyAdded $message): void
    {
        $this->logger?->info('MailNotificationMessageHandler: comment reply: ' . $message->commentReplyId);

        $reply = $this->replyRepository->find($message->commentReplyId);
        if ($reply === null) {
            return;
        }

        // a notification was already send for this comment
        if ($reply->getNotificationStatus()->hasStatus(NotificationStatus::STATUS_CREATED)) {
            return;
        }

        $comment = Type::notNull($reply->getComment());
        $review  = Type::notNull($comment->getReview());

        $this->mailService->sendNewCommentReplyMail($review, $comment, $reply);

        // update status and save
        $reply->getNotificationStatus()->addStatus(NotificationStatus::STATUS_CREATED);
        $this->replyRepository->save($reply);
    }

    /**
     * @throws Throwable
     */
    private function handleCommentResolved(CommentResolved $message): void
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
}
