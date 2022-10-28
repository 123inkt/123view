<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Entity\Asset;

use DigitalRevolution\AccessorPairConstraint\AccessorPairAsserter;
use DR\GitCommitNotification\Entity\Asset\Asset;
use DR\GitCommitNotification\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Entity\Asset\Asset
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
