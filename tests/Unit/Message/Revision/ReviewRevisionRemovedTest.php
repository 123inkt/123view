<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Message\Revision;

use DR\GitCommitNotification\Message\Revision\ReviewRevisionAdded;
use DR\GitCommitNotification\Message\Revision\ReviewRevisionRemoved;
use DR\GitCommitNotification\Tests\Unit\Message\AbstractMessageEventTestCase;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Message\Revision\ReviewRevisionRemoved
 */
class ReviewRevisionRemovedTest extends AbstractMessageEventTestCase
{
    /**
     * @covers ::__construct
     * @covers ::getName
     * @covers ::getReviewId
     * @covers ::getUserId
     * @covers ::getPayload
     */
    public function testAccessors(): void
    {
        static::assertCodeReviewEvent(
            new ReviewRevisionRemoved(5, 6, 7),
            'review-revision-removed',
            5,
            ['reviewId' => 5, 'revisionId' => 6, 'userId' => 7]
        );
        static::assertUserAware(new ReviewRevisionAdded(5, 6, 7), 7);
    }
}
