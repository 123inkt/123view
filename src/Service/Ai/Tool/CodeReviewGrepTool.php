<?php
declare(strict_types=1);

namespace DR\Review\Service\Ai\Tool;

use DR\Review\Exception\Ai\CodeReviewNotFoundException;
use DR\Review\Repository\Review\CodeReviewRepository;
use DR\Review\Service\Git\Grep\LockableGitGrepService;
use DR\Utils\Arrays;
use Psr\Log\LoggerInterface;
use Symfony\AI\Agent\Toolbox\Attribute\AsTool;
use Symfony\AI\Platform\Contract\JsonSchema\Attribute\With;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Throwable;

#[AsTool('search', 'Searches for a pattern in the codebase and returns a snippet of the matching lines.')]
class CodeReviewGrepTool
{
    public function __construct(
        #[Target('ai')] private ?LoggerInterface $logger,
        private readonly CodeReviewRepository $repository,
        private readonly LockableGitGrepService $grepService)
    {
    }

    /**
     * @param int    $codeReviewId The CODE_REVIEW_ID of the review
     * @param string $pattern      The pattern to search code for. Uses `git grep` internally.
     * @param int    $context      Show <num> leading and trailing lines, and place a line containing -- between contiguous groups of matches.
     *
     * @return string The as-is output of the git grep command
     * @throws Throwable
     */
    public function __invoke(int $codeReviewId, string $pattern, #[With(minimum: 0, maximum: 5)] int $context = 0): string
    {
        $review = $this->repository->find($codeReviewId);
        if ($review === null) {
            throw new CodeReviewNotFoundException($codeReviewId);
        }

        $revision = Arrays::lastOrNull($review->getRevisions());
        if ($revision === null) {
            throw new CodeReviewNotFoundException($codeReviewId);
        }

        $this->logger?->info(
            'CodeReviewGrepTool: Searching in review {id} for pattern "{pattern}" with context {context}',
            ['id' => $codeReviewId, 'pattern' => $pattern, 'context' => $context,]
        );

        return $this->grepService->grep($revision, $pattern, $context === 0 ? null : $context);
    }
}
