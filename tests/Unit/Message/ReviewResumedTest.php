<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Message;

use DR\GitCommitNotification\Message\ReviewResumed;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Message\ReviewResumed
 */
class ReviewResumedTest extends AbstractWebhookEventTestCase
{
    /**
     * @covers ::__construct
     * @covers ::getName
     * @covers ::getPayload
     */
    public function testAccessors(): void
    {
        static::assertWebhookEvent(new ReviewResumed(5), 'review-resumed', ['reviewId' => 5]);
    }
}
