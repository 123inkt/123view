<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Message\Review;

use DR\Review\Message\Review\ReviewRejected;
use DR\Review\Tests\Unit\Message\AbstractMessageEventTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ReviewRejected::class)]
class ReviewRejectedTest extends AbstractMessageEventTestCase
{
    public function testAccessors(): void
    {
        static::assertCodeReviewEvent(new ReviewRejected(5, 7), 'review-rejected', 5, ['reviewId' => 5, 'userId' => 7]);
        static::assertUserAware(new ReviewRejected(5, 7), 7);
    }
}
