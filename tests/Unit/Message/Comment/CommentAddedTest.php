<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Message\Comment;

use DR\GitCommitNotification\Message\Comment\CommentAdded;
use DR\GitCommitNotification\Tests\Unit\Message\AbstractMessageEventTestCase;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Message\Comment\CommentAdded
 */
class CommentAddedTest extends AbstractMessageEventTestCase
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
        static::assertCodeReviewEvent(new CommentAdded(5, 6, 7, 'message'), 'comment-added', 5, ['commentId' => 6, 'message' => 'message']);
        static::assertCommentEvent(new CommentAdded(5, 6, 7, 'message'), 6);
        static::assertUserAware(new CommentAdded(5, 6, 7, 'message'), 7);
    }
}
