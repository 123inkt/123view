<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Message;

use DR\GitCommitNotification\Message\ReviewAccepted;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Message\ReviewAccepted
 */
class ReviewAcceptedTest extends AbstractWebhookEventTestCase
{
    /**
     * @covers ::__construct
     * @covers ::getName
     * @covers ::getPayload
     */
    public function testAccessors(): void
    {
        static::assertWebhookEvent(new ReviewAccepted(5), 'review-accepted', ['reviewId' => 5]);
    }
}
