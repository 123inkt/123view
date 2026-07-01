<?php
declare(strict_types=1);

namespace DR\Review\Service\Ai\Mcp;

use DR\Review\Exception\Ai\CodeReviewNotFoundException;
use DR\Review\Repository\Mcp\CodeReviewRepository;
use DR\Review\Service\CodeReview\Comment\ResolveCommentService;
use Mcp\Capability\Attribute\McpTool;
use Symfony\AI\Platform\Contract\JsonSchema\Attribute\Schema;
use Throwable;

#[McpTool('resolve_comment', 'Resolve a comment in a code review. The reviewId must match the review the comment belongs to.')]
readonly class ResolveCommentTool
{
    public function __construct(private CodeReviewRepository $reviewRepository, private ResolveCommentService $resolveCommentService)
    {
    }

    /**
     * @throws Throwable
     */
    public function __invoke(
        #[Schema(description: 'The id of the code review the comment belongs to', minimum: 1)] int $codeReviewId,
        #[Schema(description: 'The id of the comment to resolve', minimum: 1)] int $commentId
    ): string {
        $review = $this->reviewRepository->find($codeReviewId);
        if ($review === null) {
            throw new CodeReviewNotFoundException($codeReviewId);
        }

        return $this->resolveCommentService->resolve($commentId, $codeReviewId);
    }
}
