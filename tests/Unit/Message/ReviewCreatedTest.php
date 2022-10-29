<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Message;

use DR\GitCommitNotification\Message\ReviewCreated;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Message\ReviewCreated
 */
class ReviewCreatedTest extends AbstractWebhookEventTestCase
{
    /**
     * @covers ::__construct
     * @covers ::getName
     * @covers ::getPayload
     */
    public function testAccessors(): void
    {
        static::assertWebhookEvent(new ReviewCreated(5), 'review-created', ['reviewId' => 5]);
    }
}
