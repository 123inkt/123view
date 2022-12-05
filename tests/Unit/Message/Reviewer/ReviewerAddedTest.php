<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Message\Reviewer;

use DR\Review\Message\Reviewer\ReviewerAdded;
use DR\Review\Tests\Unit\Message\AbstractMessageEventTestCase;

/**
 * @coversDefaultClass \DR\Review\Message\Reviewer\ReviewerAdded
 */
class ReviewerAddedTest extends AbstractMessageEventTestCase
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
        static::assertCodeReviewEvent(new ReviewerAdded(5, 6, 7), 'reviewer-added', 5, ['reviewId' => 5, 'userId' => 6, 'byUserId' => 7]);
        static::assertUserAware(new ReviewerAdded(5, 6, 7), 7);
    }
}
