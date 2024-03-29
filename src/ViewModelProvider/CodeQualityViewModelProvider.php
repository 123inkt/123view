<?php
declare(strict_types=1);

namespace DR\Review\ViewModelProvider;

use DR\Review\Entity\Review\CodeReview;
use DR\Review\Repository\Report\CodeCoverageFileRepository;
use DR\Review\Repository\Report\CodeInspectionIssueRepository;
use DR\Review\Repository\Report\CodeInspectionReportRepository;
use DR\Review\Service\CodeReview\CodeReviewRevisionService;
use DR\Review\ViewModel\App\Review\CodeQualityViewModel;
use DR\Utils\Assert;

class CodeQualityViewModelProvider
{
    public function __construct(
        private readonly CodeCoverageFileRepository $coverageFileRepository,
        private readonly CodeInspectionReportRepository $reportRepository,
        private readonly CodeInspectionIssueRepository $issueRepository,
        private readonly CodeReviewRevisionService $revisionService,
    ) {
    }

    public function getCodeQualityViewModel(CodeReview $review, string $filePath): CodeQualityViewModel
    {
        if ($filePath === '') {
            return new CodeQualityViewModel([], null);
        }

        $repository = Assert::notNull($review->getRepository());
        $revisions  = $this->revisionService->getRevisions($review);

        // find code inspections
        $branchIds         = $this->reportRepository->findBranchIds($repository, $revisions);
        $inspectionReports = $this->reportRepository->findByRevisions($repository, $revisions, $branchIds);
        $issues            = [];
        if (count($inspectionReports) > 0) {
            $issues = $this->issueRepository->findBy(['report' => $inspectionReports, 'file' => $filePath]);
        }

        // find code coverage
        $fileCoverage = $this->coverageFileRepository->findOneByRevisions($repository, $revisions, $filePath);

        return new CodeQualityViewModel($issues, $fileCoverage);
    }
}
