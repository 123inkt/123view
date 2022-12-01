<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Message\Reviewer;

use DR\GitCommitNotification\Message\Reviewer\ReviewerRemoved;
use DR\GitCommitNotification\Tests\Unit\Message\AbstractWebhookEventTestCase;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Message\Reviewer\ReviewerRemoved
 */
class ReviewerRemovedTest extends AbstractWebhookEventTestCase
{
    /**
     * @covers ::__construct
     * @covers ::getName
     * @covers ::getReviewId
     * @covers ::getPayload
     */
    public function testAccessors(): void
    {
        static::assertWebhookEvent(new ReviewerRemoved(5, 6, 7), 'reviewer-removed', 5, ['reviewId' => 5, 'userId' => 6, 'byUserId' => 7]);
    }
}
