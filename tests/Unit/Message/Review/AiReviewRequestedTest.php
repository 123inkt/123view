<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Message\Review;

use DR\Review\Message\Review\AiReviewRequested;
use DR\Review\Tests\Unit\Message\AbstractMessageEventTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(AiReviewRequested::class)]
class AiReviewRequestedTest extends AbstractMessageEventTestCase
{
    public function testAccessors(): void
    {
        static::assertCodeReviewEvent(new AiReviewRequested(5, 6), 'request-ai-review', 5, ['reviewId' => 5, 'userId' => 6]);
        static::assertUserAware(new AiReviewRequested(5, null), null);
        static::assertUserAware(new AiReviewRequested(5, 6), 6);
    }
}
