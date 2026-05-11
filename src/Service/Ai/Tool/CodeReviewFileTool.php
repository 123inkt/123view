<?php
declare(strict_types=1);

namespace DR\Review\Service\Ai\Tool;

use DR\Review\Exception\RepositoryException;
use DR\Review\Repository\Review\CodeReviewRepository;
use DR\Review\Service\CodeReview\CodeReviewRevisionService;
use DR\Review\Service\Git\Show\LockableGitShowService;
use DR\Utils\Arrays;
use Mcp\Capability\Attribute\McpTool;
use Mcp\Exception\ToolCallException;
use Psr\Log\LoggerInterface;
use Symfony\AI\Agent\Toolbox\Attribute\AsTool;
use Symfony\AI\Platform\Contract\JsonSchema\Attribute\Schema;

#[McpTool(
    'read_file',
    'Reads the contents of a file for the given path and review. Returns the file contents as a string. Only searches in the git ' .
    'repository of the specified code review and will not find any dependencies.'
)]
#[AsTool(
    'read_file',
    'Reads the contents of a file for the given path and review. Returns the file contents as a string. Only searches in the git ' .
    'repository of the specified code review and will not find any dependencies.'
)]
class CodeReviewFileTool
{
    public function __construct(
        private ?LoggerInterface $aiLogger,
        private readonly CodeReviewRepository $repository,
        private readonly CodeReviewRevisionService $revisionService,
        private readonly LockableGitShowService $gitShowService,
    ) {
    }

    /**
     * @throws RepositoryException
     */
    public function __invoke(
        #[Schema(description: 'The CODE_REVIEW_ID of the review', minimum: 1)] int $codeReviewId,
        #[Schema(description: 'The path to the file to read relative to the root of the git repository')] string $filepath
    ): string {
        $review = $this->repository->find($codeReviewId);
        if ($review === null) {
            throw new ToolCallException('Review not found: ' . $codeReviewId);
        }

        $revision = Arrays::lastOrNull($this->revisionService->getRevisions($review));
        if ($revision === null) {
            throw new ToolCallException('No revisions for review: ' . $codeReviewId);
        }

        $this->aiLogger?->info('CodeReviewFileTool: Reading file "{filepath}" in review {id}', ['id' => $codeReviewId, 'filepath' => $filepath]);

        return $this->gitShowService->getFileContents($revision, $filepath);
    }
}
