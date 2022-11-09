<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Message\Comment;

use DR\GitCommitNotification\Message\Comment\CommentResolved;
use DR\GitCommitNotification\Tests\Unit\Message\AbstractWebhookEventTestCase;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Message\Comment\CommentResolved
 */
class CommentResolvedTest extends AbstractWebhookEventTestCase
{
    /**
     * @covers ::__construct
     * @covers ::getName
     * @covers ::getReviewId
     * @covers ::getPayload
     */
    public function testAccessors(): void
    {
        static::assertWebhookEvent(new CommentResolved(5, 6, 7), 'comment-resolved', 5, ['reviewId' => 5]);
    }
}
