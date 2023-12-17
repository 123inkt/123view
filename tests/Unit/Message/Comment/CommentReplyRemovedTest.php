<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Message\Comment;

use DR\Review\Message\Comment\CommentReplyRemoved;
use DR\Review\Tests\Unit\Message\AbstractMessageEventTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CommentReplyRemoved::class)]
class CommentReplyRemovedTest extends AbstractMessageEventTestCase
{
    public function testAccessors(): void
    {
        static::assertCodeReviewEvent(
            new CommentReplyRemoved(5, 6, 7, 8, 9, 'message', null),
            'comment-reply-removed',
            5,
            ['commentId' => 7, 'message' => 'message']
        );
        static::assertCommentReplyEvent(new CommentReplyRemoved(5, 6, 7, 8, 9, 'message', null), 7);
        static::assertUserAware(new CommentReplyRemoved(5, 6, 7, 8, 9, 'message', null), 9);
    }
}
