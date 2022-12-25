<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Message\Comment;

use DR\Review\Message\Comment\CommentUpdated;
use DR\Review\Tests\Unit\Message\AbstractMessageEventTestCase;

/**
 * @coversDefaultClass \DR\Review\Message\Comment\CommentUpdated
 */
class CommentUpdatedTest extends AbstractMessageEventTestCase
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
            new CommentUpdated(5, 6, 7, 'file', 'message', 'original'),
            'comment-updated',
            5,
            ['commentId' => 6, 'file' => 'file', 'message' => 'message', 'originalComment' => 'original']
        );
        static::assertCommentEvent(new CommentUpdated(5, 6, 7, 'file', 'message', 'original'), 6);
        static::assertUserAware(new CommentUpdated(5, 6, 7, 'file', 'message', 'original'), 7);
    }
}
