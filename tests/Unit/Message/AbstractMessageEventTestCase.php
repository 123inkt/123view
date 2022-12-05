<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Message;

use DR\Review\Message\CodeReviewAwareInterface;
use DR\Review\Message\Comment\CommentEventInterface;
use DR\Review\Message\Comment\CommentReplyEventInterface;
use DR\Review\Message\UserAwareInterface;
use DR\Review\Tests\AbstractTestCase;

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

    protected static function assertUserAware(UserAwareInterface $event, ?int $userId): void
    {
        static::assertSame($userId, $event->getUserId());
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
