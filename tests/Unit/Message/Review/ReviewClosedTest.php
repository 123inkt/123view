<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Message\Review;

use DR\Review\Message\Review\ReviewClosed;
use DR\Review\Tests\Unit\Message\AbstractMessageEventTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ReviewClosed::class)]
class ReviewClosedTest extends AbstractMessageEventTestCase
{
    public function testAccessors(): void
    {
        static::assertCodeReviewEvent(new ReviewClosed(5, 7), 'review-closed', 5, ['reviewId' => 5, 'userId' => 7]);
        static::assertUserAware(new ReviewClosed(5, 7), 7);
    }
}
