<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Message\Review;

use DR\Review\Message\Review\ReviewResumed;
use DR\Review\Tests\Unit\Message\AbstractMessageEventTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ReviewResumed::class)]
class ReviewResumedTest extends AbstractMessageEventTestCase
{
    public function testAccessors(): void
    {
        static::assertCodeReviewEvent(new ReviewResumed(5, 7), 'review-resumed', 5, ['reviewId' => 5, 'userId' => 7]);
        static::assertUserAware(new ReviewResumed(5, 7), 7);
    }
}
