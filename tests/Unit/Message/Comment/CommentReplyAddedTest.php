<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Message\Comment;

use DR\Review\Message\Comment\CommentReplyAdded;
use DR\Review\Tests\Unit\Message\AbstractMessageEventTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CommentReplyAdded::class)]
class CommentReplyAddedTest extends AbstractMessageEventTestCase
{
    public function testAccessors(): void
    {
        static::assertCodeReviewEvent(
            new CommentReplyAdded(5, 6, 7, 'message', 'file'),
            'comment-reply-added',
            5,
            ['commentId' => 6, 'message' => 'message', 'file' => 'file']
        );
        static::assertCommentReplyEvent(new CommentReplyAdded(5, 6, 7, 'message', 'file'), 6);
        static::assertUserAware(new CommentReplyAdded(5, 6, 7, 'message', 'file'), 7);
    }
}
