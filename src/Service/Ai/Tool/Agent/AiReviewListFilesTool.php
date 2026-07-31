<?php
declare(strict_types=1);

namespace DR\Review\Service\Ai\Tool\Agent;

use DR\Review\Exception\Ai\CodeReviewFileNotFoundException;
use DR\Review\Exception\Ai\CodeReviewNotFoundException;
use DR\Review\Repository\Review\CodeReviewRepository;
use DR\Review\Service\CodeReview\CodeReviewRevisionService;
use DR\Review\Service\Git\LsTree\LockableLsTreeService;
use DR\Utils\Arrays;
use Psr\Log\LoggerInterface;
use Symfony\AI\Agent\Toolbox\Attribute\AsTool;
use Symfony\AI\Platform\Contract\JsonSchema\Attribute\Schema;
use Throwable;

/**
 * Bounded variant of directory listing for the internal review agent. Caps the number of returned
 * entries so a single tool call on a very large directory cannot exhaust the model's context window.
 */
#[AsTool(
    'list_files',
    'List the files in the given directory path for the specified code review, capped at 300 entries. Only searches in the git repository ' .
    'of the specified code review and will not find any dependencies.'
)]
class AiReviewListFilesTool
{
    public const int MAX_RESULTS = 300;

    public function __construct(
        private ?LoggerInterface $aiLogger,
        private readonly CodeReviewRepository $repository,
        private readonly CodeReviewRevisionService $revisionService,
        private readonly LockableLsTreeService $lsTreeService
    ) {
    }

    /**
     * @return array{files: string[], total: int, truncated: bool}
     * @throws Throwable
     */
    public function __invoke(#[Schema(minimum: 1)] int $codeReviewId, string $filepath): array
    {
        $review = $this->repository->find($codeReviewId);
        if ($review === null) {
            throw new CodeReviewNotFoundException($codeReviewId);
        }

        $revision = Arrays::lastOrNull($this->revisionService->getRevisions($review));
        if ($revision === null) {
            throw new CodeReviewFileNotFoundException($filepath, $codeReviewId);
        }

        $this->aiLogger?->info(
            'AiReviewListFilesTool: Listing files in "{filepath}" in review {id}',
            ['id' => $codeReviewId, 'filepath' => $filepath]
        );

        $files = $this->lsTreeService->listFiles($revision, $filepath);
        $total = count($files);

        return [
            'files'     => array_slice($files, 0, self::MAX_RESULTS),
            'total'     => $total,
            'truncated' => $total > self::MAX_RESULTS,
        ];
    }
}
