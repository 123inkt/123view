<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Message;

use DR\GitCommitNotification\Message\ReviewRejected;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Message\ReviewRejected
 */
class ReviewRejectedTest extends AbstractWebhookEventTestCase
{
    /**
     * @covers ::__construct
     * @covers ::getName
     * @covers ::getPayload
     */
    public function testAccessors(): void
    {
        static::assertWebhookEvent(new ReviewRejected(5), 'review-rejected', ['reviewId' => 5]);
    }
}
