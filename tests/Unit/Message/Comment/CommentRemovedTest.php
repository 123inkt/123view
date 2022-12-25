<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Message\Comment;

use DR\Review\Message\Comment\CommentRemoved;
use DR\Review\Tests\Unit\Message\AbstractMessageEventTestCase;

/**
 * @coversDefaultClass \DR\Review\Message\Comment\CommentRemoved
 */
class CommentRemovedTest extends AbstractMessageEventTestCase
{
    /**
     * @covers ::__construct
     * @covers ::getName
     * @covers ::getReviewId
     * @covers ::getCommentId
     * @covers ::getUserId
     * @covers ::getPayload
     */
    public function testAccessors(): void
    {
        static::assertCodeReviewEvent(
            new CommentRemoved(5, 6, 7, 'file', 'message'),
            'comment-removed',
            5,
            ['commentId' => 6, 'file' => 'file', 'message' => 'message']
        );
        static::assertCommentEvent(new CommentRemoved(5, 6, 7, 'file', 'message'), 6);
        static::assertUserAware(new CommentRemoved(5, 6, 7, 'file', 'message'), 7);
    }
}
