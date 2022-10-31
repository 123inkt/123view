<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Message\Reviewer;

use DR\GitCommitNotification\Message\Reviewer\ReviewerAdded;
use DR\GitCommitNotification\Tests\Unit\Message\AbstractWebhookEventTestCase;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Message\Reviewer\ReviewerAdded
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
