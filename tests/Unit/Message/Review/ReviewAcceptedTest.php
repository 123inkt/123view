<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Message\Review;

use DR\Review\Message\Review\ReviewAccepted;
use DR\Review\Tests\Unit\Message\AbstractMessageEventTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ReviewAccepted::class)]
class ReviewAcceptedTest extends AbstractMessageEventTestCase
{
    public function testAccessors(): void
    {
        static::assertCodeReviewEvent(new ReviewAccepted(5, 7), 'review-accepted', 5, ['reviewId' => 5, 'userId' => 7]);
        static::assertUserAware(new ReviewAccepted(5, 7), 7);
    }
}
