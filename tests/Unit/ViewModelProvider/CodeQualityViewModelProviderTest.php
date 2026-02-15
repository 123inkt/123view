<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\ViewModelProvider;

use DR\Review\Entity\Report\CodeCoverageFile;
use DR\Review\Entity\Report\CodeInspectionIssue;
use DR\Review\Entity\Report\CodeInspectionReport;
use DR\Review\Entity\Report\LineCoverage;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Repository\Report\CodeCoverageFileRepository;
use DR\Review\Repository\Report\CodeInspectionIssueRepository;
use DR\Review\Repository\Report\CodeInspectionReportRepository;
use DR\Review\Service\CodeReview\CodeReviewRevisionService;
use DR\Review\Tests\AbstractTestCase;
use DR\Review\ViewModel\App\Review\CodeQualityViewModel;
use DR\Review\ViewModelProvider\CodeQualityViewModelProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(CodeQualityViewModelProvider::class)]
class CodeQualityViewModelProviderTest extends AbstractTestCase
{
    private CodeCoverageFileRepository&MockObject     $coverageReportRepository;
    private CodeInspectionReportRepository&MockObject $reportRepository;
    private CodeInspectionIssueRepository&MockObject  $issueRepository;
    private CodeReviewRevisionService&MockObject      $revisionService;
    private CodeQualityViewModelProvider              $provider;

    protected function setUp(): void
    {
        parent::setUp();
        $this->coverageReportRepository = $this->createMock(CodeCoverageFileRepository::class);
        $this->reportRepository         = $this->createMock(CodeInspectionReportRepository::class);
        $this->issueRepository          = $this->createMock(CodeInspectionIssueRepository::class);
        $this->revisionService          = $this->createMock(CodeReviewRevisionService::class);
        $this->provider                 = new CodeQualityViewModelProvider(
            $this->coverageReportRepository,
            $this->reportRepository,
            $this->issueRepository,
            $this->revisionService
        );
    }

    public function testGetCodeQualityViewModelEmptyFilePath(): void
    {
        $this->coverageReportRepository->expects($this->never())->method('findOneByRevisions');
        $this->reportRepository->expects($this->never())->method('findByRevisions');
        $this->issueRepository->expects($this->never())->method('findBy');
        $this->revisionService->expects($this->never())->method('getRevisions');
        $review = new CodeReview();

        $viewModel = $this->provider->getCodeQualityViewModel($review, '');
        static::assertEquals(new CodeQualityViewModel([], null), $viewModel);
    }

    public function testGetCodeQualityViewModelNoReports(): void
    {
        $repository = new Repository();
        $revision   = new Revision();
        $review     = new CodeReview();
        $review->setRepository($repository);

        $this->revisionService->expects($this->once())->method('getRevisions')->with($review)->willReturn([$revision]);
        $this->reportRepository->expects($this->once())->method('findByRevisions')->with($repository, [$revision])->willReturn([]);
        $this->coverageReportRepository->expects($this->never())->method('findOneByRevisions');
        $this->issueRepository->expects($this->never())->method('findBy');

        $viewModel = $this->provider->getCodeQualityViewModel($review, 'filepath');
        static::assertEquals(new CodeQualityViewModel([], null), $viewModel);
    }

    public function testGetCodeQualityViewModel(): void
    {
        $repository = new Repository();
        $revision   = new Revision();
        $review     = new CodeReview();
        $review->setRepository($repository);

        $report       = new CodeInspectionReport();
        $issue        = (new CodeInspectionIssue())->setLineNumber(123);
        $lineCoverage = new LineCoverage();
        $coverage     = (new CodeCoverageFile())->setCoverage($lineCoverage);

        $this->coverageReportRepository->expects($this->once())->method('findOneByRevisions')
            ->with($repository, [$revision], 'filepath')
            ->willReturn($coverage);
        $this->revisionService->expects($this->once())->method('getRevisions')->with($review)->willReturn([$revision]);
        $this->reportRepository->expects($this->once())->method('findByRevisions')->with($repository, [$revision])->willReturn([$report]);
        $this->issueRepository->expects($this->once())->method('findBy')->with(['report' => [$report], 'file' => 'filepath'])->willReturn([$issue]);

        $viewModel = $this->provider->getCodeQualityViewModel($review, 'filepath');
        static::assertEquals(new CodeQualityViewModel([$issue], $coverage), $viewModel);
    }
}
