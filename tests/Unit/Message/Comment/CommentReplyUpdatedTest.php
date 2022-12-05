<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Message\Comment;

use DR\Review\Message\Comment\CommentReplyUpdated;
use DR\Review\Tests\Unit\Message\AbstractMessageEventTestCase;

/**
 * @coversDefaultClass \DR\Review\Message\Comment\CommentReplyUpdated
 */
class CommentReplyUpdatedTest extends AbstractMessageEventTestCase
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
            new CommentReplyUpdated(5, 6, 7, 'original'),
            'comment-reply-updated',
            5,
            ['commentId' => 6, 'originalComment' => 'original']
        );
        static::assertCommentReplyEvent(new CommentReplyUpdated(5, 6, 7, 'message'), 6);
        static::assertUserAware(new CommentReplyUpdated(5, 6, 7, 'message'), 7);
    }
}
