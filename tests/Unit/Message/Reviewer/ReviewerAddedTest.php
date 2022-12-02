<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Message\Reviewer;

use DR\GitCommitNotification\Message\Reviewer\ReviewerAdded;
use DR\GitCommitNotification\Tests\Unit\Message\AbstractMessageEventTestCase;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Message\Reviewer\ReviewerAdded
 */
class ReviewerAddedTest extends AbstractMessageEventTestCase
{
    /**
     * @covers ::__construct
     * @covers ::getName
     * @covers ::getReviewId
     * @covers ::getPayload
     */
    public function testAccessors(): void
    {
        static::assertCodeReviewEvent(new ReviewerAdded(5, 6, 7), 'reviewer-added', 5, ['reviewId' => 5, 'userId' => 6, 'byUserId' => 7]);
    }
}
