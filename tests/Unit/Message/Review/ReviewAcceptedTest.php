<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Message\Review;

use DR\GitCommitNotification\Message\Review\ReviewAccepted;
use DR\GitCommitNotification\Tests\Unit\Message\AbstractWebhookEventTestCase;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Message\Review\ReviewAccepted
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
