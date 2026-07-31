<?php
declare(strict_types=1);

namespace DR\Review\Service\Ai\Tool\Agent;

use DR\Review\Repository\Review\CodeReviewRepository;
use DR\Review\Service\CodeReview\CodeReviewRevisionService;
use DR\Review\Service\Git\Show\LockableGitShowService;
use DR\Utils\Arrays;
use Mcp\Exception\ToolCallException;
use Psr\Log\LoggerInterface;
use Symfony\AI\Agent\Toolbox\Attribute\AsTool;
use Symfony\AI\Platform\Contract\JsonSchema\Attribute\Schema;
use Throwable;

/**
 * Bounded, paginated variant of file reading for the internal review agent. Unlike the MCP-exposed
 * {@see \DR\Review\Service\Ai\Tool\CodeReviewFileTool}, this always caps the amount of content
 * returned per call so a single tool call cannot exhaust the model's context window.
 */
#[AsTool(
    'read_file',
    'Reads the contents of a file for the given path and review, paginated by line to protect the context window. Returns at most ' .
    '400 lines and 20000 characters per call along with pagination metadata; use offset to page through larger files. Only searches ' .
    'in the git repository of the specified code review and will not find any dependencies.'
)]
class AiReviewReadFileTool
{
    public const int MAX_LINES      = 400;
    public const int MAX_CHARACTERS = 20_000;

    public function __construct(
        private ?LoggerInterface $aiLogger,
        private readonly CodeReviewRepository $repository,
        private readonly CodeReviewRevisionService $revisionService,
        private readonly LockableGitShowService $gitShowService,
    ) {
    }

    /**
     * @return array{content: string, startLine: int, endLine: int, totalLines: int, truncated: bool}
     * @throws Throwable
     */
    public function __invoke(
        #[Schema(description: 'The CODE_REVIEW_ID of the review', minimum: 1)] int $codeReviewId,
        #[Schema(description: 'The path to the file to read relative to the root of the git repository')] string $filepath,
        #[Schema(description: 'Zero-based line number to start reading from', minimum: 0)] int $offset = 0,
        #[Schema(description: 'Maximum number of lines to return', minimum: 1, maximum: self::MAX_LINES)] int $limit = self::MAX_LINES
    ): array {
        $review = $this->repository->find($codeReviewId);
        if ($review === null) {
            throw new ToolCallException('Review not found: ' . $codeReviewId);
        }

        $revision = Arrays::lastOrNull($this->revisionService->getRevisions($review));
        if ($revision === null) {
            throw new ToolCallException('No revisions for review: ' . $codeReviewId);
        }

        $this->aiLogger?->info(
            'AiReviewReadFileTool: Reading file "{filepath}" in review {id} (offset {offset}, limit {limit})',
            ['id' => $codeReviewId, 'filepath' => $filepath, 'offset' => $offset, 'limit' => $limit]
        );

        $contents   = $this->gitShowService->getFileContents($revision, $filepath);
        $lines      = explode("\n", $contents);
        $totalLines = count($lines);

        $offset = max(0, $offset);
        $limit  = min(max(1, $limit), self::MAX_LINES);
        $slice  = array_slice($lines, $offset, $limit);
        $result = implode("\n", $slice);

        $charTruncated = strlen($result) > self::MAX_CHARACTERS;
        if ($charTruncated) {
            $result = substr($result, 0, self::MAX_CHARACTERS);
        }

        $endLine = $offset + count($slice);

        return [
            'content'    => $result,
            'startLine'  => $offset,
            'endLine'    => $endLine,
            'totalLines' => $totalLines,
            'truncated'  => $charTruncated || $endLine < $totalLines,
        ];
    }
}
