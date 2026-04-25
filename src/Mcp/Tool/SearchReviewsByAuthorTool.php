<?php

declare(strict_types=1);

namespace DR\Review\Mcp\Tool;

use DR\Review\Repository\Review\CodeReviewRepository;
use Mcp\Capability\Attribute\McpTool;
use Mcp\Capability\Attribute\Schema;

class SearchReviewsByAuthorTool
{
    public function __construct(private readonly CodeReviewRepository $reviewRepository)
    {
    }

    /**
     * Search for code reviews by the author's email address.
     *
     * @return array<int, array<string, mixed>>
     */
    #[McpTool(
        name: 'search_reviews_by_author',
        description: 'Search for code reviews that contain revisions authored by the given email address.'
    )]
    public function __invoke(
        #[Schema(type: 'string', description: 'The author\'s email address to search for.', format: 'email')]
        string $email,
    ): array {
        $reviews = $this->reviewRepository->findByAuthorEmail($email);

        return array_map(
            static fn($review) => [
                'id'         => $review->getProjectId(),
                'title'      => $review->getTitle(),
                'state'      => $review->getState(),
                'repository' => $review->getRepository()->getDisplayName(),
                'url'        => sprintf('%s/app/reviews/%s', $review->getRepository()->getName(), $review->getProjectId()),
            ],
            $reviews
        );
    }
}
