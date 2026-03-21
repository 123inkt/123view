<?php
declare(strict_types=1);

namespace DR\Review\Service\Ai\Tool;

use DR\Review\Exception\Ai\CodeReviewFileNotFoundException;
use DR\Review\Exception\Ai\CodeReviewNotFoundException;
use DR\Review\Repository\Review\CodeReviewRepository;
use DR\Review\Service\Git\LsTree\LockableLsTreeService;
use DR\Utils\Arrays;
use Psr\Log\LoggerInterface;
use Symfony\AI\Agent\Toolbox\Attribute\AsTool;
use Symfony\AI\Platform\Contract\JsonSchema\Attribute\With;
use Throwable;

#[AsTool('list_files', 'List the files in the given directory path for the specified code review.')]
class CodeReviewListFilesTool
{
    public function __construct(
        private ?LoggerInterface $aiLogger,
        private readonly CodeReviewRepository $repository,
        private readonly LockableLsTreeService $lsTreeService
    ) {
    }

    /**
     * @param int $codeReviewId The CODE_REVIEW_ID of the review
     * @param string $filepath  The path to the file to read. Glob patterns are allowed (e.g. src\/Service\/**\/*.php)
     *
     * @return string[] List of file paths
     * @throws Throwable
     */
    public function __invoke(#[With(minimum: 1)] int $codeReviewId, string $filepath): array
    {
        $review = $this->repository->find($codeReviewId);
        if ($review === null) {
            throw new CodeReviewNotFoundException($codeReviewId);
        }

        $revision = Arrays::lastOrNull($review->getRevisions());
        if ($revision === null) {
            throw new CodeReviewFileNotFoundException($filepath, $codeReviewId);
        }

        $this->aiLogger?->info(
            'CodeReviewListFilesTool: Listing files in "{filepath}" in review {id}',
            ['id' => $codeReviewId, 'filepath' => $filepath]
        );

        return $this->lsTreeService->listFiles($revision, $filepath);
    }
}
