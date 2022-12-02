<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Message;

use DR\GitCommitNotification\Message\CodeReviewAwareInterface;
use DR\GitCommitNotification\Message\Comment\CommentEventInterface;
use DR\GitCommitNotification\Message\Comment\CommentReplyEventInterface;
use DR\GitCommitNotification\Tests\AbstractTestCase;

abstract class AbstractMessageEventTestCase extends AbstractTestCase
{
    /**
     * @param array<string, int|string|bool|float|null> $payload
     */
    protected static function assertCodeReviewEvent(CodeReviewAwareInterface $event, string $name, int $reviewId, array $payload): void
    {
        static::assertSame($name, $event->getName());
        static::assertSame($reviewId, $event->getReviewId());
        static::assertSame($payload, $event->getPayload());
    }

    protected static function assertCommentEvent(CommentEventInterface $event, int $commentId): void
    {
        static::assertSame($commentId, $event->getCommentId());
    }

    protected static function assertCommentReplyEvent(CommentReplyEventInterface $event, int $commentId): void
    {
        static::assertSame($commentId, $event->getCommentReplyId());
    }
}
