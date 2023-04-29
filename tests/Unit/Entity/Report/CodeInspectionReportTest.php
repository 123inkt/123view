<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Entity\Report;

use DigitalRevolution\AccessorPairConstraint\Constraint\ConstraintConfig;
use Doctrine\Common\Collections\ArrayCollection;
use DR\Review\Entity\Report\CodeInspectionIssue;
use DR\Review\Entity\Report\CodeInspectionReport;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CodeInspectionReport::class)]
class CodeInspectionReportTest extends AbstractTestCase
{
    public function testAccessorPairs(): void
    {
        $config = (new ConstraintConfig())->setExcludedMethods(['getIssues', 'setIssues']);
        static::assertAccessorPairs(CodeInspectionReport::class, $config);
    }

    public function testSetIssues(): void
    {
        $issue  = new CodeInspectionIssue();
        $issues = new ArrayCollection([$issue]);

        $report = new CodeInspectionReport();
        $report->setIssues($issues);
        static::assertSame($issues, $report->getIssues());
    }
}
