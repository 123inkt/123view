<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Message\Review;

use DR\Review\Message\Review\ReviewCreated;
use DR\Review\Tests\Unit\Message\AbstractMessageEventTestCase;

/**
 * @coversDefaultClass \DR\Review\Message\Review\ReviewCreated
 */
class ReviewCreatedTest extends AbstractMessageEventTestCase
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
        static::assertCodeReviewEvent(new ReviewCreated(5, 6), 'review-created', 5, ['reviewId' => 5, 'revisionId' => 6]);
        static::assertUserAware(new ReviewCreated(5, 6), null);
    }
}
