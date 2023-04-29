<?php
declare(strict_types=1);

namespace DR\Review\ViewModelProvider;

use DR\Review\Entity\Review\CodeReview;
use DR\Review\Repository\Report\CodeInspectionIssueRepository;
use DR\Review\Repository\Report\CodeInspectionReportRepository;
use DR\Review\Utility\Assert;
use DR\Review\ViewModel\App\Review\CodeInspectionViewModel;

class CodeInspectionViewModelProvider
{
    public function __construct(
        private readonly CodeInspectionReportRepository $reportRepository,
        private readonly CodeInspectionIssueRepository $issueRepository
    ) {
    }

    public function getCodeInspectionViewModel(CodeReview $review, string $filePath): CodeInspectionViewModel
    {
        if ($filePath === '') {
            return new CodeInspectionViewModel([]);
        }

        $reports = $this->reportRepository->findByRevisions(Assert::notNull($review->getRepository()), $review->getRevisions()->toArray());
        if (count($reports) === 0) {
            return new CodeInspectionViewModel([]);
        }

        $issues = $this->issueRepository->findByFile($reports, $filePath);

        return new CodeInspectionViewModel($issues);
    }
}
