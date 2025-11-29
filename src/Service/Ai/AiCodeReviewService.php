<?php
declare(strict_types=1);

namespace DR\Review\Service\Ai;

use DR\Review\Entity\Git\Diff\DiffComparePolicy;
use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Model\Ai\AiCodeReview;
use DR\Review\Repository\Review\CommentRepository;
use DR\Review\Repository\User\UserRepository;
use DR\Review\Service\CodeReview\CodeReviewRevisionService;
use DR\Review\Service\Git\GitRepositoryLocationService;
use DR\Review\Service\Git\Review\FileDiffOptions;
use DR\Review\Service\Git\Review\ReviewDiffService\ReviewDiffServiceInterface;
use Psr\Log\LoggerInterface;
use Symfony\AI\Agent\AgentInterface;
use Symfony\AI\Platform\Message\Message;
use Symfony\AI\Platform\Message\MessageBag;
use Symfony\AI\Platform\Result\ObjectResult;
use Symfony\Component\Clock\ClockAwareTrait;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Throwable;

class AiCodeReviewService
{
    use ClockAwareTrait;

    private const array DISALLOWED_EXTENSIONS = ['lock', 'json'];

    public function __construct(
        #[Autowire(env: 'AI_COMMENT_USER_ID')] private readonly int $userId,
        private readonly LoggerInterface $claudeLogger,
        private readonly ReviewDiffServiceInterface $diffService,
        private readonly CodeReviewRevisionService $revisionService,
        private readonly UserRepository $userRepository,
        private readonly CommentRepository $commentRepository,
        private readonly GitRepositoryLocationService $repositoryLocationService,
        private readonly AgentInterface $agent,
    ) {
    }

    /**
     * @throws Throwable
     */
    public function requestCodeReview(CodeReview $review): string
    {
        // gather revisions
        $revisions = $this->revisionService->getRevisions($review);

        // get diff files for review
        $files = $this->diffService->getDiffForRevisions(
            $review->getRepository(),
            $revisions,
            new FileDiffOptions(5, DiffComparePolicy::IGNORE_EMPTY_LINES, includeRaw: true)
        );

        // filter out large and non-essential files
        $files = array_filter($files, static function (DiffFile $file) {
            if (str_contains($file->getPathname(), 'baseline')) {
                return false;
            }
            if (in_array(strtolower((string)$file->getFile()?->getExtension()), self::DISALLOWED_EXTENSIONS, true)) {
                return false;
            }
            if ($file->isImage() || $file->isDeleted()) {
                return false;
            }
            if (count($file->getLines()) > 500) {
                return false;
            }

            return true;
        });
        if (count($files) === 0) {
            $this->claudeLogger->info('No suitable files found for code review, skipping review {reviewId}', ['reviewId' => $review->getId()]);

            return '';
        }

        // get the diffs
        $diff = implode("\n", array_map(fn(DiffFile $file) => $file->raw, $files));
        $message = "CODE_REVIEW_ID: " . $review->getId() . "\n";

        $response = $this->agent->call(
            new MessageBag(Message::ofUser($message . $diff)),
            ['response_format' => AiCodeReview::class]
        );

        if ($response instanceof ObjectResult === false) {
            return '';
        }

        $result = $response->getContent();

        return '';

        //if ($result instanceof AiCodeReview === false) {
        //    $this->claudeLogger->info(
        //        'Code review response is not of expected type {type}',
        //        ['type' => is_object($result) ? $result::class : gettype($result), 'reviewId' => $review->getId()]
        //    );
        //}

        $this->claudeLogger->info('Code review response {response}', ['response' => $result, 'reviewId' => $review->getId()]);

        //$user = Assert::notNull($this->userRepository->find($this->userId));

        //foreach ($responses as $response) {
        //    $comment = new Comment();
        //    $comment->setFilePath($response->filepath);
        //    $comment->setTag(null);
        //    $comment->setLineReference(
        //        new LineReference(
        //            oldPath: $response->filepath, newPath: $response->filepath,
        //            line   : $response->lineNumber, lineAfter: $response->lineNumber
        //        )
        //    );
        //    $comment->setReview($review);
        //    $comment->setMessage($response->message);
        //    $comment->setUser($user);
        //    $comment->setCreateTimestamp($this->now()->getTimestamp());
        //    $comment->setUpdateTimestamp($this->now()->getTimestamp());
        //
        //    $review->getComments()->add($comment);
        //    $this->commentRepository->save($comment, true);
        //}
    }
}
