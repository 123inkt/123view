<?php
declare(strict_types=1);

namespace DR\Review\Service\Api\Anthropic;

use DR\Review\Entity\Git\Diff\DiffComparePolicy;
use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\LineReference;
use DR\Review\Repository\Review\CommentRepository;
use DR\Review\Repository\User\UserRepository;
use DR\Review\Service\CodeReview\CodeReviewRevisionService;
use DR\Review\Service\Git\GitRepositoryLocationService;
use DR\Review\Service\Git\Review\FileDiffOptions;
use DR\Review\Service\Git\Review\ReviewDiffService\ReviewDiffServiceInterface;
use DR\Utils\Assert;
use Psr\Log\LoggerInterface;
use Symfony\Component\Clock\ClockAwareTrait;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Throwable;

class AnthropicCodeReview
{
    use ClockAwareTrait;

    private const array ALLOWED_EXTENSIONS = ['php', 'twig', 'scss', 'ts'];

    public function __construct(
        #[Autowire(env: 'ANTHROPIC_COMMENT_USER_ID')] private readonly int $userId,
        private readonly LoggerInterface $claudeLogger,
        private readonly ReviewDiffServiceInterface $diffService,
        private readonly AnthropicPromptService $promptService,
        private readonly CodeReviewRevisionService $revisionService,
        private readonly UserRepository $userRepository,
        private readonly CommentRepository $commentRepository,
        private readonly GitRepositoryLocationService $repositoryLocationService,
        private readonly AnthropicResponseParser $responseParser,
    ) {
    }

    /**
     * @throws Throwable
     */
    public function requestCodeReview(CodeReview $review): void
    {
        // gather revisions
        $revisions = $this->revisionService->getRevisions($review);

        // get diff files for review
        $files = $this->diffService->getDiffForRevisions(
            $review->getRepository(),
            $revisions,
            new FileDiffOptions(
                FileDiffOptions::DEFAULT_LINE_DIFF,
                DiffComparePolicy::IGNORE_EMPTY_LINES,
                includeRaw: true
            )
        );

        // filter out large and non-essential files
        $files = array_filter($files, static function (DiffFile $file) {
            if (str_contains($file->getPathname(), 'baseline')) {
                return false;
            }
            if (in_array(strtolower((string)$file->getFile()?->getExtension()), self::ALLOWED_EXTENSIONS, true) === false) {
                return false;
            }
            if ($file->isDeleted()) {
                return false;
            }
            if (count($file->getLines()) > 500) {
                return false;
            }

            return true;
        });
        if (count($files) === 0) {
            $this->claudeLogger->info('No suitable files found for code review, skipping review {reviewId}', ['reviewId' => $review->getId()]);

            return;
        }

        // get the diffs
        $diffs = array_map(fn(DiffFile $file) => $file->raw, $files);

        // try to get agents file from repository
        $agentsMdPath = $this->repositoryLocationService->getLocation($review->getRepository()) . '/AGENTS.md';
        $agentsMd     = file_exists($agentsMdPath) ? file_get_contents($agentsMdPath) : null;

        // execute the prompts
        $result = $this->promptService->prompt("Review the follow code.\n" . implode("\n", $diffs), $agentsMd);

        $this->claudeLogger->info('Code review response {response}', ['response' => $result, 'reviewId' => $review->getId()]);

        $responses = $this->responseParser->parse($result->message);
        $user      = Assert::notNull($this->userRepository->find($this->userId));

        foreach ($responses as $response) {
            $comment = new Comment();
            $comment->setFilePath($response->filepath);
            $comment->setTag(null);
            $comment->setLineReference(new LineReference(newPath: $response->filepath, lineAfter: $response->lineNumber));
            $comment->setReview($review);
            $comment->setMessage($response->message);
            $comment->setUser($user);
            $comment->setCreateTimestamp($this->now()->getTimestamp());
            $comment->setUpdateTimestamp($this->now()->getTimestamp());

            $review->getComments()->add($comment);
            $this->commentRepository->save($comment, true);
        }
    }
}
