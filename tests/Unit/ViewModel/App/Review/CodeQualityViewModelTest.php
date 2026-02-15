<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\ViewModel\App\Review;

use DR\Review\Entity\Report\CodeCoverageFile;
use DR\Review\Entity\Report\CodeInspectionIssue;
use DR\Review\Entity\Report\LineCoverage;
use DR\Review\Tests\AbstractTestCase;
use DR\Review\ViewModel\App\Review\CodeQualityViewModel;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CodeQualityViewModel::class)]
class CodeQualityViewModelTest extends AbstractTestCase
{
    public function testGetIssues(): void
    {
        $issue = new CodeInspectionIssue();
        $issue->setLineNumber(500);

        $viewModel = new CodeQualityViewModel([$issue], null);

        static::assertSame([], $viewModel->getIssues(null));
        static::assertSame([], $viewModel->getIssues(400));
        static::assertSame([$issue], $viewModel->getIssues(500));
    }

    public function testGetCoveragePercentage(): void
    {
        $viewModel = new CodeQualityViewModel([], null);
        static::assertNull($viewModel->getCoveragePercentage());

        $fileCoverage = new CodeCoverageFile();
        $fileCoverage->setPercentage('123.45');
        $fileCoverage->setCoverage(static::createStub(LineCoverage::class));

        $viewModel = new CodeQualityViewModel([], $fileCoverage);
        static::assertSame(123.45, $viewModel->getCoveragePercentage());
    }

    public function testGetCoverage(): void
    {
        $lineCoverage = new LineCoverage();
        $lineCoverage->setCoverage(5, 1);
        $lineCoverage->setCoverage(10, 0);

        $fileCoverage = new CodeCoverageFile();
        $fileCoverage->setCoverage($lineCoverage);

        $viewModel = new CodeQualityViewModel([], $fileCoverage);

        static::assertSame(-1, $viewModel->getCoverage(null));
        static::assertSame(1, $viewModel->getCoverage(5));
        static::assertSame(0, $viewModel->getCoverage(10));
    }

    public function testGetCoverageAbsentCoverage(): void
    {
        $viewModel = new CodeQualityViewModel([], null);

        static::assertNull($viewModel->getCoverage(5));
    }
}
