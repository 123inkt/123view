<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\MessageHandler;

use DR\GitCommitNotification\Message\Comment\CommentAdded;
use DR\GitCommitNotification\Message\Comment\CommentReplyAdded;
use DR\GitCommitNotification\Message\Comment\CommentResolved;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;

#[AsMessageHandler(fromTransport: 'async_delay_mail')]
class MailNotificationMessageHandler implements MessageSubscriberInterface
{
    public function __construct(private readonly LoggerInterface $logger)
    {
    }

    public function handleCommentAdded(CommentAdded $message): void
    {
        $this->logger->info('comment added: ' . $message->commentId);
    }

    public function handleCommentReplyAdded(CommentReplyAdded $message): void
    {
        $this->logger->info('comment reply: ' . $message->commentReplyId);
    }

    public function handleCommentResolved(CommentResolved $message): void
    {
        $this->logger->info('comment resolved: ' . $message->commentId);
    }

    /**
     * @return iterable<string, array<string, string>>
     */
    public static function getHandledMessages(): iterable
    {
        yield CommentAdded::class => ['method' => 'handleCommentAdded'];
        yield CommentReplyAdded::class => ['method' => 'handleCommentReplyAdded'];
        yield CommentResolved::class => ['method' => 'handleCommentResolved'];
    }
}
