<?php
declare(strict_types=1);

namespace DR\Review\ViewModelProvider;

use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Model\Review\DirectoryTreeNode;
use DR\Review\Repository\Report\CodeInspectionIssueRepository;
use DR\Review\Repository\Report\CodeInspectionReportRepository;
use DR\Review\ViewModel\App\Review\CodeInspectionReportViewModel;
use DR\Review\ViewModel\App\Review\ReviewSummaryViewModel;

class ReviewSummaryViewModelProvider
{
    public function __construct(
        private readonly ReviewTimelineViewModelProvider $timelineViewModelProvider,
        private readonly CodeInspectionReportRepository $reportRepository,
        private readonly CodeInspectionIssueRepository $issueRepository
    ) {
    }

    /**
     * @param Revision[]                  $revisions
     * @param DirectoryTreeNode<DiffFile> $fileTree
     */
    public function getSummaryViewModel(CodeReview $review, array $revisions, DirectoryTreeNode $fileTree): ReviewSummaryViewModel
    {
        $repository = $review->getRepository();
        $branchIds  = $this->reportRepository->findBranchIds($repository, $revisions);
        $reports    = $this->reportRepository->findByRevisions($repository, $revisions, $branchIds);
        $issues     = [];
        if (count($reports) > 0) {
            $filePaths = array_map(static fn(DiffFile $file) => $file->getPathname(), $fileTree->getFilesRecursive());
            $issues    = $this->issueRepository->findBy(['report' => $reports, 'file' => $filePaths], ['file' => 'ASC']);
        }

        return new ReviewSummaryViewModel(
            $this->timelineViewModelProvider->getTimelineViewModel($review, $revisions),
            new CodeInspectionReportViewModel($issues)
        );
    }
}
