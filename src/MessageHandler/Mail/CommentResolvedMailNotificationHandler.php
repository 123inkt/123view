<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\MessageHandler\Mail;

use DR\GitCommitNotification\Doctrine\Type\CommentStateType;
use DR\GitCommitNotification\Entity\Review\NotificationStatus;
use DR\GitCommitNotification\Message\Comment\CommentResolved;
use DR\GitCommitNotification\Message\MailNotificationInterface;
use DR\GitCommitNotification\Repository\Review\CommentRepository;
use DR\GitCommitNotification\Repository\User\UserRepository;
use DR\GitCommitNotification\Service\Mail\CommentMailService;
use DR\GitCommitNotification\Utility\Assert;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Throwable;

class CommentResolvedMailNotificationHandler implements MailNotificationHandlerInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private readonly CommentMailService $mailService,
        private readonly CommentRepository $commentRepository,
        private readonly UserRepository $userRepository
    ) {
    }

    /**
     * @throws Throwable
     */
    public function handle(MailNotificationInterface $message): void
    {
        assert($message instanceof CommentResolved);
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

    public static function accepts(): string
    {
        return CommentResolved::class;
    }
}
