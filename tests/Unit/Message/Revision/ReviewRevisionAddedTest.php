<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Message\Revision;

use DR\GitCommitNotification\Message\Revision\ReviewRevisionAdded;
use DR\GitCommitNotification\Tests\Unit\Message\AbstractWebhookEventTestCase;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Message\Revision\ReviewRevisionAdded
 */
class ReviewRevisionAddedTest extends AbstractWebhookEventTestCase
{
    /**
     * @covers ::__construct
     * @covers ::getName
     * @covers ::getReviewId
     * @covers ::getPayload
     */
    public function testAccessors(): void
    {
        static::assertWebhookEvent(
            new ReviewRevisionAdded(5, 6, 7),
            'review-revision-added',
            5,
            ['reviewId' => 5, 'revisionId' => 6, 'userId' => 7]
        );
    }
}
