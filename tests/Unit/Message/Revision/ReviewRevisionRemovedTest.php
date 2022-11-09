<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Message\Revision;

use DR\GitCommitNotification\Message\Revision\ReviewRevisionRemoved;
use DR\GitCommitNotification\Tests\Unit\Message\AbstractWebhookEventTestCase;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Message\Revision\ReviewRevisionRemoved
 */
class ReviewRevisionRemovedTest extends AbstractWebhookEventTestCase
{
    /**
     * @covers ::__construct
     * @covers ::getName
     * @covers ::getReviewId
     * @covers ::getPayload
     */
    public function testAccessors(): void
    {
        static::assertWebhookEvent(new ReviewRevisionRemoved(5, 6), 'review-revision-removed', 5, ['reviewId' => 5, 'revisionId' => 6]);
    }
}
