<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\MessageHandler\Mail;

use Traversable;

/**
 * @uses \DR\GitCommitNotification\MessageHandler\Mail\CommentAddedMailNotificationHandler
 * @uses \DR\GitCommitNotification\MessageHandler\Mail\CommentReplyAddedMailNotificationHandler
 * @uses \DR\GitCommitNotification\MessageHandler\Mail\CommentUpdatedMailNotificationHandler
 * @uses \DR\GitCommitNotification\MessageHandler\Mail\CommentReplyUpdatedMailNotificationHandler
 */
class MailNotificationHandlerProvider
{
    /** @var MailNotificationHandlerInterface[] */
    private array $handlers;

    /**
     * @param Traversable<MailNotificationHandlerInterface> $handlers
     */
    public function __construct(Traversable $handlers)
    {
        $this->handlers = iterator_to_array($handlers);
    }

    public function getHandler(string $className): ?MailNotificationHandlerInterface
    {
        return $this->handlers[$className] ?? null;
    }
}
