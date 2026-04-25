<?php

declare(strict_types=1);

namespace DR\Review\Service\Ai\Mcp;

use DR\Review\Model\Mcp\CodeReviewQuery;
use DR\Review\Repository\Mcp\CodeReviewRepository;
use Mcp\Capability\Attribute\McpTool;
use Mcp\Capability\Attribute\Schema;

class GetCodeReviewsTool
{
    public function __construct(private readonly CodeReviewRepository $reviewRepository)
    {
    }

    /**
     * @return list<array<string, int|string|null>>
     */
    #[McpTool(
        name: 'get-code-reviews',
        description: 'Search for code reviews using optional filters. All provided filters are applied as AND conditions. Returns up to 50 results ordered by most recently updated.'
    )]
    public function __invoke(
        #[Schema(type: 'string', description: 'Filter by (partial) review title.')]
        ?string $title = null,
        #[Schema(type: 'string', description: 'Filter by exact branch name of the revision.')]
        ?string $branchName = null,
        #[Schema(type: 'string', description: 'Filter by exact author email address of the revision.', format: 'email')]
        ?string $author = null,
        #[Schema(type: 'string', description: 'Filter by exact repository URL (http/https).', format: 'uri')]
        ?string $repositoryUrl = null,
    ): array {
        $reviews = $this->reviewRepository->findByFilters(new CodeReviewQuery($title, $branchName, $author, $repositoryUrl), 50);

        return array_values(array_map(
            static fn($review) => [
                'id'            => $review->getProjectId(),
                'title'         => $review->getTitle(),
                'state'         => $review->getState(),
                'reviewerState' => $review->getReviewersState(),
                'repository'    => $review->getRepository()->getDisplayName(),
                'url'           => sprintf('%s/app/reviews/%s', $review->getRepository()->getName(), $review->getProjectId()),
            ],
            $reviews
        ));
    }
}
