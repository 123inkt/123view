<?php
declare(strict_types=1);

namespace DR\Review\MessageHandler\Mail;

use Traversable;

/**
 * @uses CommentAddedMailNotificationHandler
 * @uses CommentReplyAddedMailNotificationHandler
 * @uses CommentUpdatedMailNotificationHandler
 * @uses CommentReplyUpdatedMailNotificationHandler
 * @uses CommentResolvedMailNotificationHandler
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
