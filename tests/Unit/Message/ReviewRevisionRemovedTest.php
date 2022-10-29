<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Message;

use DR\GitCommitNotification\Message\ReviewRevisionRemoved;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Message\ReviewRevisionRemoved
 */
class ReviewRevisionRemovedTest extends AbstractWebhookEventTestCase
{
    /**
     * @covers ::__construct
     * @covers ::getName
     * @covers ::getPayload
     */
    public function testAccessors(): void
    {
        static::assertWebhookEvent(new ReviewRevisionRemoved(5, 6), 'review-revision-removed', ['reviewId' => 5, 'revisionId' => 6]);
    }
}
