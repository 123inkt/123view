<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Message\Review;

use DR\GitCommitNotification\Message\Review\ReviewClosed;
use DR\GitCommitNotification\Tests\Unit\Message\AbstractWebhookEventTestCase;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Message\Review\ReviewClosed
 */
class ReviewClosedTest extends AbstractWebhookEventTestCase
{
    /**
     * @covers ::__construct
     * @covers ::getName
     * @covers ::getReviewId
     * @covers ::getPayload
     */
    public function testAccessors(): void
    {
        static::assertWebhookEvent(new ReviewClosed(5), 'review-closed', 5, ['reviewId' => 5]);
    }
}
