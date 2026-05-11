<?php

declare(strict_types=1);

namespace DR\Review\Service\Ai\Tool;

use DR\Review\Doctrine\Type\CodeReviewStateType;
use DR\Review\Model\Mcp\CodeReviewQuery;
use DR\Review\Model\Mcp\CodeReviewResult;
use DR\Review\Repository\Mcp\CodeReviewRepository;
use Mcp\Capability\Attribute\McpTool;
use Mcp\Capability\Attribute\Schema;

#[McpTool(
    name       : 'get_code_review',
    description: 'Find the first code review matching the given filters. All provided filters are applied as AND conditions. ' .
    'Returns null when no match is found.'
)]
readonly class GetCodeReviewTool
{
    public function __construct(private CodeReviewRepository $reviewRepository)
    {
    }

    public function __invoke(
        #[Schema(type: 'string', description: 'Filter by (partial) review title.')]
        ?string $title = null,
        #[Schema(type: 'string', description: 'Filter by exact branch name of the revision.')]
        ?string $branchName = null,
        #[Schema(type: 'string', description: 'Filter by exact author email address of the revision.', format: 'email')]
        ?string $author = null,
        #[Schema(type: 'string', description: 'Filter by exact repository URL', format: 'uri')]
        ?string $repositoryUrl = null,
        #[Schema(type: 'string', description: 'Filter by review state.', enum: [CodeReviewStateType::OPEN, CodeReviewStateType::CLOSED])]
        ?string $state = null,
    ): ?CodeReviewResult {
        $review = $this->reviewRepository->findByFilters(new CodeReviewQuery($title, $branchName, $author, $repositoryUrl, $state), 1)[0] ?? null;
        if ($review === null) {
            return null;
        }

        return new CodeReviewResult(
            $review->getId(),
            $review->getTitle(),
            $review->getState(),
            $review->getReviewersState(),
            $review->getRepository()->getDisplayName()
        );
    }
}
