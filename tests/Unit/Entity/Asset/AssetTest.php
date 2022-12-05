<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Entity\Asset;

use DigitalRevolution\AccessorPairConstraint\AccessorPairAsserter;
use DR\Review\Entity\Asset\Asset;
use DR\Review\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\Review\Entity\Asset\Asset
 */
class AssetTest extends AbstractTestCase
{
    use AccessorPairAsserter;

    /**
     * @covers ::<public>
     */
    public function testAccessorPairs(): void
    {
        static::assertAccessorPairs(Asset::class);
    }
}
