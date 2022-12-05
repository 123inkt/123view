<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Message\Comment;

use DR\GitCommitNotification\Message\Comment\CommentUpdated;
use DR\GitCommitNotification\Tests\Unit\Message\AbstractMessageEventTestCase;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Message\Comment\CommentUpdated
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
            new CommentUpdated(5, 6, 7, 'original'),
            'comment-updated',
            5,
            ['comment-id' => 6, 'original-comment' => 'original']
        );
        static::assertCommentEvent(new CommentUpdated(5, 6, 7, 'message'), 6);
        static::assertUserAware(new CommentUpdated(5, 6, 7, 'message'), 7);
    }
}
