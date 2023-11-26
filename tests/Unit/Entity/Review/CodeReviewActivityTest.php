<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Entity\Review;

use DR\Review\Entity\Review\CodeReviewActivity;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CodeReviewActivity::class)]
class CodeReviewActivityTest extends AbstractTestCase
{
    public function testAccessorPairs(): void
    {
        static::assertAccessorPairs(CodeReviewActivity::class);
    }

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
