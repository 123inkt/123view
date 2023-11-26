<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Message\Review;

use DR\Review\Message\Review\ReviewCreated;
use DR\Review\Tests\Unit\Message\AbstractMessageEventTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ReviewCreated::class)]
class ReviewCreatedTest extends AbstractMessageEventTestCase
{
    public function testAccessors(): void
    {
        static::assertCodeReviewEvent(new ReviewCreated(5, 6, 7), 'review-created', 5, ['reviewId' => 5, 'revisionId' => 6, 'userId' => 7]);
        static::assertUserAware(new ReviewCreated(5, 6, null), null);
        static::assertUserAware(new ReviewCreated(5, 6, 7), 7);
    }
}
