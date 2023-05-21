<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\ViewModelProvider;

use DR\Review\Entity\Report\CodeInspectionIssue;
use DR\Review\Entity\Report\CodeInspectionReport;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Repository\Report\CodeInspectionIssueRepository;
use DR\Review\Repository\Report\CodeInspectionReportRepository;
use DR\Review\Service\CodeReview\CodeReviewRevisionService;
use DR\Review\Tests\AbstractTestCase;
use DR\Review\ViewModel\App\Review\CodeInspectionViewModel;
use DR\Review\ViewModelProvider\CodeInspectionViewModelProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(CodeInspectionViewModelProvider::class)]
class CodeInspectionViewModelProviderTest extends AbstractTestCase
{
    private CodeInspectionReportRepository&MockObject $reportRepository;
    private CodeInspectionIssueRepository&MockObject  $issueRepository;
    private CodeReviewRevisionService&MockObject      $revisionService;
    private CodeInspectionViewModelProvider           $provider;

    protected function setUp(): void
    {
        parent::setUp();
        $this->reportRepository = $this->createMock(CodeInspectionReportRepository::class);
        $this->issueRepository  = $this->createMock(CodeInspectionIssueRepository::class);
        $this->revisionService  = $this->createMock(CodeReviewRevisionService::class);
        $this->provider         = new CodeInspectionViewModelProvider($this->reportRepository, $this->issueRepository, $this->revisionService);
    }

    public function testGetCodeInspectionViewModelEmptyFilePath(): void
    {
        $review = new CodeReview();

        $viewModel = $this->provider->getCodeInspectionViewModel($review, '');
        static::assertEquals(new CodeInspectionViewModel([]), $viewModel);
    }

    public function testGetCodeInspectionViewModelNoReports(): void
    {
        $repository = new Repository();
        $revision   = new Revision();
        $review     = new CodeReview();
        $review->setRepository($repository);

        $this->revisionService->expects(self::once())->method('getRevisions')->with($review)->willReturn([$revision]);
        $this->reportRepository->expects(self::once())->method('findByRevisions')->with($repository, [$revision])->willReturn([]);

        $viewModel = $this->provider->getCodeInspectionViewModel($review, 'filepath');
        static::assertEquals(new CodeInspectionViewModel([]), $viewModel);
    }

    public function testGetCodeInspectionViewModel(): void
    {
        $repository = new Repository();
        $revision   = new Revision();
        $review     = new CodeReview();
        $review->setRepository($repository);

        $report = new CodeInspectionReport();
        $issue  = new CodeInspectionIssue();

        $this->revisionService->expects(self::once())->method('getRevisions')->with($review)->willReturn([$revision]);
        $this->reportRepository->expects(self::once())->method('findByRevisions')->with($repository, [$revision])->willReturn([$report]);
        $this->issueRepository->expects(self::once())->method('findBy')->with(['report' => [$report], 'file' => 'filepath'])->willReturn([$issue]);

        $viewModel = $this->provider->getCodeInspectionViewModel($review, 'filepath');
        static::assertEquals(new CodeInspectionViewModel([$issue]), $viewModel);
    }
}
