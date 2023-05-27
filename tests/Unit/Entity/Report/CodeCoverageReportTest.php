<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Entity\Report;

use DigitalRevolution\AccessorPairConstraint\Constraint\ConstraintConfig;
use Doctrine\Common\Collections\ArrayCollection;
use DR\Review\Entity\Report\CodeCoverageReport;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CodeCoverageReport::class)]
class CodeCoverageReportTest extends AbstractTestCase
{
    /**
     * @covers ::<public>
     */
    public function testAccessorPairs(): void
    {
        $config = (new ConstraintConfig())->setExcludedMethods(['getFiles']);
        static::assertAccessorPairs(CodeCoverageReport::class, $config);
    }

    public function testGetFiles(): void
    {
        $collection = new ArrayCollection();
        $report     = new CodeCoverageReport();

        $report->setFiles($collection);
        static::assertSame($collection, $report->getFiles());
    }
}
