<?php

declare(strict_types=1);

namespace DR\Review\Service\Ai\Mcp;

use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Exception\Ai\CodeReviewNotFoundException;
use DR\Review\Repository\Mcp\CodeReviewRepository;
use DR\Review\Service\CodeReview\CodeReviewDiffService;
use Mcp\Capability\Attribute\McpTool;
use Symfony\AI\Platform\Contract\JsonSchema\Attribute\Schema;
use Throwable;

#[McpTool('get_code_review_diff', 'Get the diff for all the changes done in the review')]
readonly class GetCodeReviewDiffTool
{
    public function __construct(private CodeReviewRepository $reviewRepository, private CodeReviewDiffService $diffService)
    {
    }

    /**
     * @throws Throwable
     */
    public function __invoke(#[Schema(description: 'The review id of the code review', minimum: 1)] int $codeReviewId): string
    {
        $review = $this->reviewRepository->find($codeReviewId);
        if ($review === null) {
            throw new CodeReviewNotFoundException($codeReviewId);
        }

        $files = $this->diffService->getDiff($review);
        if (count($files) === 0) {
            return 'No changes found in this review.';
        }

        return implode("\n", array_map(static fn(DiffFile $file) => $file->raw, $files));
    }
}
