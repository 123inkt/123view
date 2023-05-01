<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\ViewModel\App\Review;

use DR\Review\Entity\Report\CodeInspectionIssue;
use DR\Review\Tests\AbstractTestCase;
use DR\Review\ViewModel\App\Review\CodeInspectionReportViewModel;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CodeInspectionReportViewModel::class)]
class CodeInspectionReportViewModelTest extends AbstractTestCase
{
    public function testGetGroupByFile(): void
    {
        $issueA = new CodeInspectionIssue();
        $issueA->setFile('first-file');

        $issueB = new CodeInspectionIssue();
        $issueB->setFile('first-file');

        $issueC = new CodeInspectionIssue();
        $issueC->setFile('second-file');

        $model  = new CodeInspectionReportViewModel([$issueA, $issueB, $issueC]);
        $result = $model->getGroupByFile();
        static::assertSame([$issueA, $issueB], $result['first-file']);
        static::assertSame([$issueC], $result['second-file']);
    }
}
