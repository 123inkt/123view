<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\MessageHandler;

use DR\GitCommitNotification\Doctrine\Type\CommentStateType;
use DR\GitCommitNotification\Entity\Review\NotificationStatus;
use DR\GitCommitNotification\Message\Comment\CommentAdded;
use DR\GitCommitNotification\Message\Comment\CommentReplyAdded;
use DR\GitCommitNotification\Message\Comment\CommentReplyUpdated;
use DR\GitCommitNotification\Message\Comment\CommentResolved;
use DR\GitCommitNotification\Message\Comment\CommentUpdated;
use DR\GitCommitNotification\Message\Delay\DelayableMessage;
use DR\GitCommitNotification\Message\MailNotificationInterface;
use DR\GitCommitNotification\Repository\Review\CommentReplyRepository;
use DR\GitCommitNotification\Repository\Review\CommentRepository;
use DR\GitCommitNotification\Repository\User\UserRepository;
use DR\GitCommitNotification\Service\CodeReview\Comment\CommentMentionService;
use DR\GitCommitNotification\Service\Mail\MailService;
use DR\GitCommitNotification\Utility\Arrays;
use DR\GitCommitNotification\Utility\Assert;
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
        private readonly CommentMentionService $mentionService,
        private readonly MessageBusInterface $bus,
        private readonly int $mailNotificationDelay
    ) {
    }

    /**
     * Stage 1: any mail notification message should be resubmitted with a xxx seconds delay
     * @throws Throwable
     */
    public function delayMessage(MailNotificationInterface $message): void
    {
        $this->logger?->info(
            sprintf('MailNotificationMessageHandler: delay message for %d seconds: %s', $this->mailNotificationDelay / 1000, get_class($message))
        );

        $this->bus->dispatch(new Envelope(new DelayableMessage($message), [new DelayStamp($this->mailNotificationDelay)]));
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
        } elseif ($message->message instanceof CommentUpdated) {
            $this->handleCommentUpdated($message->message);
        } elseif ($message->message instanceof CommentReplyAdded) {
            $this->handleCommentReplyAdded($message->message);
        } elseif ($message->message instanceof CommentReplyUpdated) {
            $this->handleCommentReplyUpdated($message->message);
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

        $this->mailService->sendNewCommentMail(Assert::notNull($comment->getReview()), $comment);

        // update status and save
        $comment->getNotificationStatus()->addStatus(NotificationStatus::STATUS_CREATED);
        $this->commentRepository->save($comment);
    }

    /**
     * @throws Throwable
     */
    private function handleCommentUpdated(CommentUpdated $message): void
    {
        $this->logger?->info('MailNotificationMessageHandler: comment updated: ' . $message->commentId);

        $comment = $this->commentRepository->find($message->commentId);
        if ($comment === null) {
            // comment was removed before we could send it
            return;
        }

        $mentions = array_values($this->mentionService->getMentionedUsers((string)$comment->getMessage()));
        if (count($mentions) === 0) {
            return;
        }

        $originalMentions = $this->mentionService->getMentionedUsers($message->originalComment);
        $newMentions      = Arrays::unique(Arrays::diff($mentions, $originalMentions));

        if (count($newMentions) === 0) {
            return;
        }

        $this->logger?->info('MailNotificationMessageHandler: sending new mentions to comment');

        $this->mailService->sendNewCommentMail(Assert::notNull($comment->getReview()), $comment, $newMentions);
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

        $comment = Assert::notNull($reply->getComment());
        $review  = Assert::notNull($comment->getReview());

        $this->mailService->sendNewCommentReplyMail($review, $comment, $reply);

        // update status and save
        $reply->getNotificationStatus()->addStatus(NotificationStatus::STATUS_CREATED);
        $this->replyRepository->save($reply);
    }

    /**
     * @throws Throwable
     */
    private function handleCommentReplyUpdated(CommentReplyUpdated $message): void
    {
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

        $this->mailService->sendCommentResolvedMail(Assert::notNull($comment->getReview()), $comment, $user);

        // update status and save
        $comment->getNotificationStatus()->addStatus(NotificationStatus::STATUS_RESOLVED);
        $this->commentRepository->save($comment);
    }
}
