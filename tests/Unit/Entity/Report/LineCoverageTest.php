<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Entity\Report;

use DR\Review\Entity\Report\LineCoverage;
use DR\Review\Tests\AbstractTestCase;
use Nette\Utils\JsonException;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(LineCoverage::class)]
class LineCoverageTest extends AbstractTestCase
{
    public function testAccessors(): void
    {
        $lineCoverage = new LineCoverage();
        $lineCoverage->setCoverage(5, 10);
        $lineCoverage->setCoverage(6, 0);

        static::assertSame(10, $lineCoverage->getCoverage(5));
        static::assertSame(0, $lineCoverage->getCoverage(6));
        static::assertNull($lineCoverage->getCoverage(7));
    }

    public function testGetPercentage(): void
    {
        static::assertSame(100.0, (new LineCoverage())->getPercentage());
        static::assertSame(100.0, (new LineCoverage())->setCoverage(5, 1)->setCoverage(6, 1)->getPercentage());
        static::assertSame(50.0, (new LineCoverage())->setCoverage(5, 1)->setCoverage(6, 0)->getPercentage());
        static::assertSame(0.0, (new LineCoverage())->setCoverage(5, 0)->setCoverage(6, 0)->getPercentage());
    }

    /**
     * @throws JsonException
     */
    public function testFromBinaryString(): void
    {
        $lineCoverage = new LineCoverage();
        $lineCoverage->setCoverage(5, 10);
        $lineCoverage->setCoverage(10, 20);
        $lineCoverage->setCoverage(30, 40);

        $data            = $lineCoverage->toBinaryString();
        $newLineCoverage = LineCoverage::fromBinaryString($data);

        static::assertEquals($lineCoverage, $newLineCoverage);
    }

    /**
     * @throws JsonException
     */
    public function testFromBinaryStringEmptyValue(): void
    {
        $lineCoverage = new LineCoverage();

        $data = $lineCoverage->toBinaryString();
        static::assertSame('', $data);
        $newLineCoverage = LineCoverage::fromBinaryString($data);

        static::assertEquals($lineCoverage, $newLineCoverage);
    }
}
