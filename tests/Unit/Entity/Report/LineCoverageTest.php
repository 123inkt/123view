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
}
