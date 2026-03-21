<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Entity\Asset;

use DigitalRevolution\AccessorPairConstraint\AccessorPairAsserter;
use DR\Review\Entity\Asset\Asset;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Asset::class)]
class AssetTest extends AbstractTestCase
{
    use AccessorPairAsserter;

    public function testAccessorPairs(): void
    {
        static::assertAccessorPairs(Asset::class);
    }

    public function testGetHash(): void
    {
        $asset = (new Asset())->setData('foobar');
        static::assertSame('c3ab8ff1', $asset->getHash());
    }
}
