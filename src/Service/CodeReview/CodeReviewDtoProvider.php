<?php
declare(strict_types=1);

namespace DR\Review\Service\CodeReview;

use DR\Review\Entity\Review\CodeReview;
use DR\Review\Model\Review\CodeReviewDto;
use DR\Review\Repository\Review\CodeReviewRepository;
use DR\Review\Request\Review\ReviewRequest;
use DR\Review\Service\Git\Review\CodeReviewTypeDecider;
use DR\Review\Service\Git\Review\FileDiffOptions;
use DR\Review\Service\Revision\RevisionVisibilityService;
use Throwable;

readonly class CodeReviewDtoProvider
{
    public function __construct(
        private CodeReviewRevisionService $revisionService,
        private CodeReviewFileService $fileService,
        private CodeReviewTypeDecider $reviewTypeDecider,
        private RevisionVisibilityService $visibilityService,
        private CodeReviewRepository $codeReviewRepository,
    ) {
    }

    /**
     * @throws Throwable
     */
    public function provide(CodeReview $review, ReviewRequest $request): CodeReviewDto
    {
        $revisions        = $this->revisionService->getRevisions($review);
        $visibleRevisions = $this->visibilityService->getVisibleRevisions($review, $revisions);
        $similarReviews   = $this->codeReviewRepository->findByTitle($review);

        // get diff files for review
        $reviewType = $this->reviewTypeDecider->decide($review, $revisions, $visibleRevisions);
        [$fileTree, $selectedFile] = $this->fileService->getFiles(
            $review,
            $visibleRevisions,
            $request->getFilePath(),
            new FileDiffOptions(
                FileDiffOptions::DEFAULT_LINE_DIFF,
                $request->getComparisonPolicy(),
                $reviewType,
                $request->getVisibleLines()
            )
        );

        return new CodeReviewDto(
            $review,
            $similarReviews,
            $revisions,
            $visibleRevisions,
            $fileTree,
            $selectedFile,
            $request->getFilePath(),
            $request->getTab(),
            $request->getComparisonPolicy(),
            $request->getDiffMode(),
            $request->getAction(),
            $request->getVisibleLines()
        );
    }
}
