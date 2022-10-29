<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Message;

use DR\GitCommitNotification\Message\ReviewOpened;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Message\ReviewOpened
 */
class ReviewOpenedTest extends AbstractWebhookEventTestCase
{
    /**
     * @covers ::__construct
     * @covers ::getName
     * @covers ::getPayload
     */
    public function testAccessors(): void
    {
        static::assertWebhookEvent(new ReviewOpened(5), 'review-opened', ['reviewId' => 5]);
    }
}
