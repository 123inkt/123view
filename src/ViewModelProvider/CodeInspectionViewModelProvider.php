<?php
declare(strict_types=1);

namespace DR\Review\ViewModelProvider;

use DR\Review\Entity\Review\CodeReview;
use DR\Review\Repository\Report\CodeInspectionIssueRepository;
use DR\Review\Repository\Report\CodeInspectionReportRepository;
use DR\Review\Service\CodeReview\CodeReviewRevisionService;
use DR\Review\Utility\Assert;
use DR\Review\ViewModel\App\Review\CodeInspectionViewModel;

class CodeInspectionViewModelProvider
{
    public function __construct(
        private readonly CodeInspectionReportRepository $reportRepository,
        private readonly CodeInspectionIssueRepository $issueRepository,
        private readonly CodeReviewRevisionService $revisionService,
    ) {
    }

    public function getCodeInspectionViewModel(CodeReview $review, string $filePath): CodeInspectionViewModel
    {
        if ($filePath === '') {
            return new CodeInspectionViewModel([]);
        }

        $revisions = $this->revisionService->getRevisions($review);
        $reports   = $this->reportRepository->findByRevisions(Assert::notNull($review->getRepository()), $revisions);
        if (count($reports) === 0) {
            return new CodeInspectionViewModel([]);
        }

        $issues = $this->issueRepository->findBy(['report' => $reports, 'file' => $filePath]);

        return new CodeInspectionViewModel($issues);
    }
}
