<?php

declare(strict_types=1);

namespace DR\Review\Service\Ai\Mcp;

use DR\Review\Exception\Ai\InvalidReviewUrlException;
use DR\Review\Exception\Ai\RepositoryNotFoundException;
use DR\Review\Exception\Ai\ReviewNotFoundForUrlException;
use DR\Review\Doctrine\Type\CodeReviewType;
use DR\Review\Model\Mcp\CodeReviewResult;
use DR\Review\Repository\Config\RepositoryRepository;
use DR\Review\Repository\Mcp\CodeReviewRepository;
use DR\Review\Service\CodeReview\CodeReviewRevisionService;
use Mcp\Capability\Attribute\McpTool;
use Symfony\AI\Platform\Contract\JsonSchema\Attribute\Schema;

#[McpTool(
    'get_review_id_from_url',
    'Resolve a 123view review URL (e.g. https://<host>/app/<repository>/review/cr-<number>) to its code review. ' .
    'Use this first when given a review URL, then pass the returned id to other review tools such as get_code_review_diff.'
)]
readonly class GetReviewIdFromUrlTool
{
    private const string URL_PATTERN = '#^(?:https?://[^/]+)?/app/([a-z][a-z0-9-]*[a-z0-9])/review/cr-(\d+)#';

    public function __construct(
        private RepositoryRepository $repositoryRepository,
        private CodeReviewRepository $reviewRepository,
        private CodeReviewRevisionService $revisionService,
    ) {
    }

    public function __invoke(
        #[Schema(description: 'The full 123view review URL, e.g. https://<host>/app/<repository>/review/cr-<number>')]
        string $url
    ): CodeReviewResult {
        if (preg_match(self::URL_PATTERN, $url, $matches) !== 1) {
            throw new InvalidReviewUrlException($url);
        }

        $repositoryName = $matches[1];
        $projectId      = (int)$matches[2];

        $repository = $this->repositoryRepository->findOneBy(['name' => $repositoryName]);
        if ($repository === null) {
            throw new RepositoryNotFoundException($repositoryName);
        }

        $review = $this->reviewRepository->findOneBy(['repository' => $repository, 'projectId' => $projectId]);
        if ($review === null) {
            throw new ReviewNotFoundForUrlException($repositoryName, $projectId);
        }

        if ($review->getType() === CodeReviewType::BRANCH) {
            $revisions = $this->revisionService->getRevisions($review);
            $firstHash = $revisions[0]?->getCommitHash() ?? '';
            $lastHash  = $revisions[array_key_last($revisions)]?->getCommitHash() ?? '';
        } else {
            $firstHash = $review->getRevisions()->first()?->getCommitHash() ?? '';
            $lastHash  = $review->getRevisions()->last()?->getCommitHash() ?? '';
        }

        return new CodeReviewResult(
            $review->getId(),
            $review->getTitle(),
            $review->getState(),
            $review->getReviewersState(),
            $review->getRepository()->getDisplayName(),
            $firstHash,
            $lastHash,
            $review->getType() === CodeReviewType::BRANCH ? 'branch' : 'commit',
        );
    }
}
