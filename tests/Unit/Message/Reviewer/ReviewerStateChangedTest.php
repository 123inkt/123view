<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Message\Reviewer;

use DR\Review\Message\Reviewer\ReviewerStateChanged;
use DR\Review\Tests\Unit\Message\AbstractMessageEventTestCase;

/**
 * @coversDefaultClass \DR\Review\Message\Reviewer\ReviewerStateChanged
 */
class ReviewerStateChangedTest extends AbstractMessageEventTestCase
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
            new ReviewerStateChanged(5, 6, 7, 'old', 'new'),
            'reviewer-state-changed',
            5,
            [
                'reviewId' => 5,
                'reviewerId' => 6,
                'userId' => 7,
                'oldState' => 'old',
                'newState' => 'new'
            ]
        );
        static::assertUserAware(new ReviewerStateChanged(5, 6, 7, 'old', 'new'), 7);
    }
}
