<?php
declare(strict_types=1);

namespace DR\Review\Service\Ai\Tool;

use DR\Review\Exception\Ai\CodeReviewNotFoundException;
use DR\Review\Repository\Review\CodeReviewRepository;
use DR\Review\Service\Git\Grep\LockableGitGrepService;
use DR\Utils\Arrays;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\AI\Agent\Toolbox\Attribute\AsTool;
use Symfony\AI\Platform\Contract\JsonSchema\Attribute\With;
use Throwable;

#[AsTool(
    'search',
    'Searches for a pattern in the codebase of the project and returns a snippet of the matching lines. Only searches in the git repository of the ' .
    'specified code review and will not find any dependencies.'
)]
class CodeReviewGrepTool
{
    public function __construct(
        private ?LoggerInterface $aiLogger,
        private readonly CodeReviewRepository $repository,
        private readonly LockableGitGrepService $grepService
    ) {
    }

    /**
     * @param int $codeReviewId The CODE_REVIEW_ID of the review
     * @param string $pattern   The pattern to search code for. _Must_ be correct regex pattern. Uses `git grep` internally.
     * @param int $context      Show <num> leading and trailing lines, and place a line containing -- between contiguous groups of matches.
     *
     * @return string The as-is output of the git grep command
     * @throws Throwable
     */
    public function __invoke(int $codeReviewId, string $pattern, #[With(minimum: 0, maximum: 5)] int $context = 0): string
    {
        if (@preg_match('/' . $pattern . '/', '') === false) {
            throw new InvalidArgumentException('The provided pattern is not a valid regex pattern: ' . $pattern);
        }

        $review = $this->repository->find($codeReviewId);
        if ($review === null) {
            throw new CodeReviewNotFoundException($codeReviewId);
        }

        $revision = Arrays::lastOrNull($review->getRevisions());
        if ($revision === null) {
            throw new CodeReviewNotFoundException($codeReviewId);
        }

        $this->aiLogger?->info(
            'CodeReviewGrepTool: Searching in review {id} for pattern "{pattern}" with context {context}',
            ['id' => $codeReviewId, 'pattern' => $pattern, 'context' => $context,]
        );

        $result = $this->grepService->grep($revision, $pattern, $context === 0 ? null : $context);
        if ($result === null) {
            $this->aiLogger?->info(
                'CodeReviewGrepTool: No results found for pattern "{pattern}" in review {id}',
                ['pattern' => $pattern, 'id' => $codeReviewId]
            );
            return 'No results found';
        }
        return $result;
    }
}
