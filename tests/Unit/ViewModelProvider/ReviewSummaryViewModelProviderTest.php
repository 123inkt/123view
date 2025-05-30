<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\ViewModelProvider;

use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Entity\Report\CodeInspectionIssue;
use DR\Review\Entity\Report\CodeInspectionReport;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Model\Review\DirectoryTreeNode;
use DR\Review\Repository\Report\CodeInspectionIssueRepository;
use DR\Review\Repository\Report\CodeInspectionReportRepository;
use DR\Review\Tests\AbstractTestCase;
use DR\Review\ViewModelProvider\ReviewSummaryViewModelProvider;
use DR\Review\ViewModelProvider\ReviewTimelineViewModelProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(ReviewSummaryViewModelProvider::class)]
class ReviewSummaryViewModelProviderTest extends AbstractTestCase
{
    private ReviewTimelineViewModelProvider&MockObject $timelineModelProvider;
    private CodeInspectionReportRepository&MockObject  $reportRepository;
    private CodeInspectionIssueRepository&MockObject   $issueRepository;
    private ReviewSummaryViewModelProvider             $provider;

    protected function setUp(): void
    {
        parent::setUp();
        $this->timelineModelProvider = $this->createMock(ReviewTimelineViewModelProvider::class);
        $this->reportRepository      = $this->createMock(CodeInspectionReportRepository::class);
        $this->issueRepository       = $this->createMock(CodeInspectionIssueRepository::class);
        $this->provider              = new ReviewSummaryViewModelProvider(
            $this->timelineModelProvider,
            $this->reportRepository,
            $this->issueRepository
        );
    }

    public function testGetSummaryViewModel(): void
    {
        $repository = new Repository();
        $revision   = new Revision();
        $review     = new CodeReview();
        $review->setRepository($repository);

        $file                 = new DiffFile();
        $file->filePathBefore = 'file/path/before';
        $file->filePathAfter  = 'file/path/after';
        $tree                 = new DirectoryTreeNode('name', null, [], [$file]);

        $report = new CodeInspectionReport();
        $issue  = new CodeInspectionIssue();

        $this->reportRepository->expects($this->once())->method('findByRevisions')->with($repository, [$revision])->willReturn([$report]);
        $this->issueRepository->expects($this->once())
            ->method('findBy')
            ->with(['report' => [$report], 'file' => ['file/path/after']], ['file' => 'ASC'])
            ->willReturn([$issue]);
        $this->timelineModelProvider->expects($this->once())->method('getTimelineViewModel')->with($review);

        $this->provider->getSummaryViewModel($review, [$revision], $tree);
    }
}
