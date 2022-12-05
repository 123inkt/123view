<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Message\Comment;

use DR\Review\Message\Comment\CommentResolved;
use DR\Review\Tests\Unit\Message\AbstractMessageEventTestCase;

/**
 * @coversDefaultClass \DR\Review\Message\Comment\CommentResolved
 */
class CommentResolvedTest extends AbstractMessageEventTestCase
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
        static::assertCodeReviewEvent(new CommentResolved(5, 6, 7), 'comment-resolved', 5, ['commentId' => 6, 'resolvedByUserId' => 7]);
        static::assertCommentEvent(new CommentResolved(5, 6, 7), 6);
        static::assertUserAware(new CommentResolved(5, 6, 7), 7);
    }
}
