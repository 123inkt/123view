<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Message\Review;

use DR\Review\Message\Review\ReviewOpened;
use DR\Review\Tests\Unit\Message\AbstractMessageEventTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ReviewOpened::class)]
class ReviewOpenedTest extends AbstractMessageEventTestCase
{
    public function testAccessors(): void
    {
        static::assertCodeReviewEvent(new ReviewOpened(5, 7), 'review-opened', 5, ['reviewId' => 5, 'userId' => 7]);
        static::assertUserAware(new ReviewOpened(5, 7), 7);
    }
}
