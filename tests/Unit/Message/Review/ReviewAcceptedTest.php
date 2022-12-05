<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Message\Review;

use DR\Review\Message\Review\ReviewAccepted;
use DR\Review\Tests\Unit\Message\AbstractMessageEventTestCase;

/**
 * @coversDefaultClass \DR\Review\Message\Review\ReviewAccepted
 */
class ReviewAcceptedTest extends AbstractMessageEventTestCase
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
        static::assertCodeReviewEvent(new ReviewAccepted(5, 7), 'review-accepted', 5, ['reviewId' => 5, 'userId' => 7]);
        static::assertUserAware(new ReviewAccepted(5, 7), 7);
    }
}
