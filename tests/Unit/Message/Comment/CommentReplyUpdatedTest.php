<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Message\Comment;

use DR\GitCommitNotification\Message\Comment\CommentReplyUpdated;
use DR\GitCommitNotification\Tests\Unit\Message\AbstractMessageEventTestCase;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Message\Comment\CommentReplyUpdated
 */
class CommentReplyUpdatedTest extends AbstractMessageEventTestCase
{
    /**
     * @covers ::__construct
     * @covers ::getName
     * @covers ::getReviewId
     * @covers ::getPayload
     */
    public function testAccessors(): void
    {
        static::assertCodeReviewEvent(
            new CommentReplyUpdated(5, 6, 'original'),
            'comment-reply-updated',
            5,
            ['comment-id' => 6, 'original-comment' => 'original']
        );
        static::assertCommentReplyEvent(new CommentReplyUpdated(5, 6, 'message'), 6);
    }
}
