<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Message\Review;

use DR\GitCommitNotification\Message\Review\ReviewCreated;
use DR\GitCommitNotification\Tests\Unit\Message\AbstractMessageEventTestCase;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Message\Review\ReviewCreated
 */
class ReviewCreatedTest extends AbstractMessageEventTestCase
{
    /**
     * @covers ::__construct
     * @covers ::getName
     * @covers ::getReviewId
     * @covers ::getPayload
     */
    public function testAccessors(): void
    {
        static::assertCodeReviewEvent(new ReviewCreated(5), 'review-created', 5, ['reviewId' => 5]);
    }
}
