<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Message\Comment;

use DR\Review\Message\Comment\CommentReplyAdded;
use DR\Review\Tests\Unit\Message\AbstractMessageEventTestCase;

/**
 * @coversDefaultClass \DR\Review\Message\Comment\CommentReplyAdded
 */
class CommentReplyAddedTest extends AbstractMessageEventTestCase
{
    /**
     * @covers ::__construct
     * @covers ::getName
     * @covers ::getReviewId
     * @covers ::getCommentReplyId
     * @covers ::getUserId
     * @covers ::getPayload
     */
    public function testAccessors(): void
    {
        static::assertCodeReviewEvent(
            new CommentReplyAdded(5, 6, 7, 'message'),
            'comment-reply-added',
            5,
            ['commentId' => 6, 'message' => 'message']
        );
        static::assertCommentReplyEvent(new CommentReplyAdded(5, 6, 7, 'message'), 6);
        static::assertUserAware(new CommentReplyAdded(5, 6, 7, 'message'), 7);
    }
}
