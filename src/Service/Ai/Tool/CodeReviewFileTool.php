<?php
declare(strict_types=1);

namespace DR\Review\Service\Ai\Tool;

use DR\Review\Exception\Ai\CodeReviewFileNotFoundException;
use DR\Review\Exception\Ai\CodeReviewNotFoundException;
use DR\Review\Exception\RepositoryException;
use DR\Review\Repository\Review\CodeReviewRepository;
use DR\Review\Service\Git\Show\LockableGitShowService;
use DR\Utils\Arrays;
use Psr\Log\LoggerInterface;
use Symfony\AI\Agent\Toolbox\Attribute\AsTool;
use Symfony\AI\Platform\Contract\JsonSchema\Attribute\With;

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
        private readonly LockableGitShowService $gitShowService,
    ) {
    }

    /**
     * @param int    $codeReviewId The CODE_REVIEW_ID of the review
     * @param string $filepath     The path to the file to read
     *
     * @throws RepositoryException
     */
    public function __invoke(#[With(minimum: 1)] int $codeReviewId, string $filepath): string
    {
        $review = $this->repository->find($codeReviewId);
        if ($review === null) {
            throw new CodeReviewNotFoundException($codeReviewId);
        }

        $revision = Arrays::lastOrNull($review->getRevisions());
        if ($revision === null) {
            throw new CodeReviewFileNotFoundException($filepath, $codeReviewId);
        }

        $this->aiLogger?->info('CodeReviewFileTool: Reading file "{filepath}" in review {id}', ['id' => $codeReviewId, 'filepath' => $filepath]);

        return $this->gitShowService->getFileContents($revision, $filepath);
    }
}
