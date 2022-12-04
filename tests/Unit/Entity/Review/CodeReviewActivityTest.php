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

    /**
     * @covers ::getDataValue
     */
    public function testGetDataValue(): void
    {
        $activity = new CodeReviewActivity();
        $activity->setData(['bool' => true, 'int' => 1, 'float' => 1.1, 'string' => 'string', 'null' => null]);
        static::assertTrue($activity->getDataValue('bool'));
        static::assertSame(1, $activity->getDataValue('int'));
        static::assertSame(1.1, $activity->getDataValue('float'));
        static::assertSame('string', $activity->getDataValue('string'));
        static::assertNull($activity->getDataValue('null'));
    }
}
