<?php
declare(strict_types=1);

namespace DR\Review\Service\CodeReview;

use DR\Review\Entity\Review\CodeReview;
use DR\Review\Model\Review\ReviewDto;
use DR\Review\Request\Review\ReviewRequest;
use DR\Review\Service\Git\Review\CodeReviewTypeDecider;
use DR\Review\Service\Git\Review\FileDiffOptions;
use DR\Review\Service\Revision\RevisionVisibilityService;

readonly class ReviewDtoProvider
{
    public function __construct(
        private CodeReviewRevisionService $revisionService,
        private CodeReviewFileService $fileService,
        private CodeReviewTypeDecider $reviewTypeDecider,
        private RevisionVisibilityService $visibilityService,
    ) {
    }

    public function provide(CodeReview $review, ReviewRequest $request): ReviewDto
    {
        $revisions        = $this->revisionService->getRevisions($review);
        $visibleRevisions = $this->visibilityService->getVisibleRevisions($review, $revisions);

        // get diff files for review
        $reviewType = $this->reviewTypeDecider->decide($review, $revisions, $visibleRevisions);
        [$fileTree, $selectedFile] = $this->fileService->getFiles(
            $review,
            $visibleRevisions,
            $request->getFilePath(),
            new FileDiffOptions(FileDiffOptions::DEFAULT_LINE_DIFF, $request->getComparisonPolicy(), $reviewType)
        );

        return new ReviewDto(
            $review,
            $revisions,
            $visibleRevisions,
            $fileTree,
            $selectedFile,
            $request->getFilePath(),
            $request->getTab(),
            $request->getComparisonPolicy(),
            $request->getDiffMode(),
            $request->getAction()
        );
    }
}
