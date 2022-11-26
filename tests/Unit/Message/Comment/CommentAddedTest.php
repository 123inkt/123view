<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Message\Comment;

use DR\GitCommitNotification\Message\Comment\CommentAdded;
use DR\GitCommitNotification\Tests\Unit\Message\AbstractWebhookEventTestCase;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Message\Comment\CommentAdded
 */
class CommentAddedTest extends AbstractWebhookEventTestCase
{
    /**
     * @covers ::__construct
     * @covers ::getName
     * @covers ::getReviewId
     * @covers ::getPayload
     */
    public function testAccessors(): void
    {
        static::assertWebhookEvent(new CommentAdded(5, 6), 'comment-added', 5, ['comment-id' => 6]);
    }
}
