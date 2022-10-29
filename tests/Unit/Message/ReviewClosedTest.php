<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Message;

use DR\GitCommitNotification\Message\ReviewClosed;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Message\ReviewClosed
 */
class ReviewClosedTest extends AbstractWebhookEventTestCase
{
    /**
     * @covers ::__construct
     * @covers ::getName
     * @covers ::getPayload
     */
    public function testAccessors(): void
    {
        static::assertWebhookEvent(new ReviewClosed(5), 'review-closed', ['reviewId' => 5]);
    }
}
