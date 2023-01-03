<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Message\Revision;

use DR\Review\Message\Revision\ReviewRevisionAdded;
use DR\Review\Tests\Unit\Message\AbstractMessageEventTestCase;

/**
 * @coversDefaultClass \DR\Review\Message\Revision\ReviewRevisionAdded
 */
class ReviewRevisionAddedTest extends AbstractMessageEventTestCase
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
            new ReviewRevisionAdded(5, 6, 7, 'title'),
            'review-revision-added',
            5,
            ['reviewId' => 5, 'revisionId' => 6, 'userId' => 7, 'title' => 'title']
        );
        static::assertUserAware(new ReviewRevisionAdded(5, 6, 7, 'title'), 7);
    }
}
