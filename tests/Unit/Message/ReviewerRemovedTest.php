<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Message;

use DR\GitCommitNotification\Message\ReviewerRemoved;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Message\ReviewerRemoved
 */
class ReviewerRemovedTest extends AbstractWebhookEventTestCase
{
    /**
     * @covers ::__construct
     * @covers ::getName
     * @covers ::getPayload
     */
    public function testAccessors(): void
    {
        static::assertWebhookEvent(new ReviewerRemoved(5, 6), 'reviewer-removed', ['reviewId' => 5, 'userId' => 6]);
    }
}
