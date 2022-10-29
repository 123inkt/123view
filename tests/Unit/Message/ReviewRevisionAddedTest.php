<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Message;

use DR\GitCommitNotification\Message\ReviewRevisionAdded;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Message\ReviewRevisionAdded
 */
class ReviewRevisionAddedTest extends AbstractWebhookEventTestCase
{
    /**
     * @covers ::__construct
     * @covers ::getName
     * @covers ::getPayload
     */
    public function testAccessors(): void
    {
        static::assertWebhookEvent(new ReviewRevisionAdded(5, 6), 'review-revision-added', ['reviewId' => 5, 'revisionId' => 6]);
    }
}
