<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Message\Comment;

use DR\GitCommitNotification\Message\Comment\CommentReplyUpdated;
use DR\GitCommitNotification\Tests\Unit\Message\AbstractWebhookEventTestCase;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Message\Comment\CommentReplyUpdated
 */
class CommentReplyUpdatedTest extends AbstractWebhookEventTestCase
{
    /**
     * @covers ::__construct
     * @covers ::getName
     * @covers ::getReviewId
     * @covers ::getPayload
     */
    public function testAccessors(): void
    {
        static::assertWebhookEvent(
            new CommentReplyUpdated(5, 6, 'original'),
            'comment-reply-updated',
            5,
            ['comment-id' => 6, 'original-comment' => 'original']
        );
    }
}
