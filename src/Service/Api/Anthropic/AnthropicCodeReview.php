<?php
declare(strict_types=1);

namespace DR\Review\Service\Api\Anthropic;

use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\LineReference;
use DR\Review\Repository\Review\CommentRepository;
use DR\Review\Repository\User\UserRepository;
use DR\Review\Service\CodeReview\CodeReviewRevisionService;
use DR\Review\Service\Git\Review\ReviewDiffService\ReviewDiffServiceInterface;
use DR\Utils\Assert;
use Psr\Log\LoggerInterface;
use Symfony\Component\Clock\ClockAwareTrait;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Throwable;

class AnthropicCodeReview
{
    use ClockAwareTrait;

    public function __construct(
        #[Autowire(env: 'ANTHROPIC_COMMENT_USER_ID')] private readonly int $userId,
        private readonly LoggerInterface $claudeLogger,
        private readonly ReviewDiffServiceInterface $diffService,
        private readonly AnthropicPromptService $promptService,
        private readonly CodeReviewRevisionService $revisionService,
        private readonly UserRepository $userRepository,
        private readonly CommentRepository $commentRepository,
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
        $files = $this->diffService->getDiffForRevisions($review->getRepository(), $revisions);

        // filter out large and non-essential files
        $files = array_filter($files, static function (DiffFile $file) {
            if (str_contains($file->getPathname(), 'baseline')) {
                return false;
            }
            if ($file->getFile()?->getExtension() !== 'php') {
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
        // get the diffs
        $diffs = array_map(fn(DiffFile $file) => $file->raw, $files);

        // execute the prompts
        $result = $this->promptService->prompt("Review the follow code.\n" . implode("\n", $diffs));

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
