<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Entity\Review;

use DR\GitCommitNotification\Entity\Review\CodeReviewActivity;
use DR\GitCommitNotification\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Entity\Review\CodeReviewActivity
 */
class CodeReviewActivityTest extends AbstractTestCase
{
    /**
     * @covers ::<public>
     */
    public function testAccessorPairs(): void
    {
        static::assertAccessorPairs(CodeReviewActivity::class);
    }
}
