<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Message\Comment;

use DR\GitCommitNotification\Message\Comment\CommentReplyAdded;
use DR\GitCommitNotification\Tests\Unit\Message\AbstractWebhookEventTestCase;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Message\Comment\CommentReplyAdded
 */
class CommentReplyAddedTest extends AbstractWebhookEventTestCase
{
    /**
     * @covers ::__construct
     * @covers ::getName
     * @covers ::getReviewId
     * @covers ::getPayload
     */
    public function testAccessors(): void
    {
        static::assertWebhookEvent(new CommentReplyAdded(5, 6), 'comment-reply-added', 5, ['comment-id' => 6]);
    }
}
