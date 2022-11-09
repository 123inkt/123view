<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Message\Review;

use DR\GitCommitNotification\Message\Review\ReviewResumed;
use DR\GitCommitNotification\Tests\Unit\Message\AbstractWebhookEventTestCase;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Message\Review\ReviewResumed
 */
class ReviewResumedTest extends AbstractWebhookEventTestCase
{
    /**
     * @covers ::__construct
     * @covers ::getName
     * @covers ::getReviewId
     * @covers ::getPayload
     */
    public function testAccessors(): void
    {
        static::assertWebhookEvent(new ReviewResumed(5), 'review-resumed', 5, ['reviewId' => 5]);
    }
}
