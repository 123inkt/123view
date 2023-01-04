<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Message\Comment;

use DR\Review\Message\Comment\CommentUnresolved;
use DR\Review\Tests\Unit\Message\AbstractMessageEventTestCase;

/**
 * @coversDefaultClass \DR\Review\Message\Comment\CommentUnresolved
 */
class CommentUnresolvedTest extends AbstractMessageEventTestCase
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
            new CommentUnresolved(5, 6, 7, 'file'),
            'comment-unresolved',
            5,
            ['commentId' => 6, 'file' => 'file', 'unresolvedByUserId' => 7]
        );
        static::assertCommentEvent(new CommentUnresolved(5, 6, 7, 'file'), 6);
        static::assertUserAware(new CommentUnresolved(5, 6, 7, 'file'), 7);
    }
}
