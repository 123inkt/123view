<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Message;

use DR\GitCommitNotification\Message\ReviewerAdded;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Message\ReviewerAdded
 */
class ReviewerAddedTest extends AbstractWebhookEventTestCase
{
    /**
     * @covers ::__construct
     * @covers ::getName
     * @covers ::getPayload
     */
    public function testAccessors(): void
    {
        static::assertWebhookEvent(new ReviewerAdded(5, 6), 'reviewer-added', ['reviewId' => 5, 'userId' => 6]);
    }
}
