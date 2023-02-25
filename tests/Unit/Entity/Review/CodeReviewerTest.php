<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Entity\Review;

use DR\Review\Entity\Review\CodeReviewer;
use DR\Review\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\Review\Entity\Review\CodeReviewer
 */
class CodeReviewerTest extends AbstractTestCase
{
    /**
     * @covers ::setId
     * @covers ::getId
     * @covers ::getState
     * @covers ::setState
     * @covers ::getStateTimestamp
     * @covers ::setStateTimestamp
     * @covers ::getReview
     * @covers ::setReview
     * @covers ::getUser
     * @covers ::setUser
     */
    public function testAccessorPairs(): void
    {
        static::assertNull((new CodeReviewer())->getId());
        static::assertAccessorPairs(CodeReviewer::class);
    }
}
