<?php
declare(strict_types=1);

namespace DR\Review\Service\Ai\Tool;

use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\LineReference;
use DR\Review\Entity\Review\NotificationStatus;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Exception\Ai\CodeReviewNotFoundException;
use DR\Review\Repository\Review\CodeReviewRepository;
use DR\Review\Repository\Review\CommentRepository;
use DR\Review\Repository\User\UserRepository;
use DR\Review\Service\CodeReview\CodeReviewRevisionService;
use DR\Utils\Arrays;
use DR\Utils\Assert;
use Psr\Log\LoggerInterface;
use Symfony\AI\Agent\Toolbox\Attribute\AsTool;
use Symfony\AI\Platform\Contract\JsonSchema\Attribute\With;
use Symfony\Component\Clock\ClockAwareTrait;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Throwable;

#[AsTool('add_comment', 'Add a comment to a code review at a specific file and line number. Optionally include a code suggestion.')]
class CodeReviewAddCommentTool
{
    use ClockAwareTrait;

    public function __construct(
        #[Autowire(env: 'AI_COMMENT_USER_ID')] private readonly ?int $userId,
        private ?LoggerInterface $aiLogger,
        private readonly CodeReviewRepository $repository,
        private readonly UserRepository $userRepository,
        private readonly CommentRepository $commentRepository,
        private readonly CodeReviewRevisionService $reviewRevisionService
    ) {
    }

    /**
     * @param int     $codeReviewId   The CODE_REVIEW_ID of the review
     * @param string  $filepath       The path of the file to comment on, must match the file path from the diff
     * @param int     $lineNumber     The line number in the file to comment on
     * @param string  $message        The comment text to add
     * @param ?string $codeSuggestion Optional code suggestion to include in the comment
     *
     * @throws Throwable
     */
    public function __invoke(
        #[With(minimum: 1)] int $codeReviewId,
        string $filepath,
        #[With(minimum: 1)] int $lineNumber,
        string $message,
        ?string $codeSuggestion
    ): string {
        $review = $this->repository->find($codeReviewId);
        if ($review === null) {
            throw new CodeReviewNotFoundException($codeReviewId);
        }

        if ($codeSuggestion !== null && $codeSuggestion !== '') {
            $message .= "\n\n```\n" . $codeSuggestion . "\n```";
        }

        // markdown bold + : turn in to kiss emoticon. Replace it to bold only.
        $message = str_replace(':**', '**', $message);

        $this->aiLogger?->info(
            'CodeReviewAddCommentTool: Adding comment to file "{filepath}" at line {line} in review {id}',
            ['id' => $codeReviewId, 'filepath' => $filepath, 'line' => $lineNumber]
        );

        /** @var Revision $revision */
        $revision = Arrays::last($this->reviewRevisionService->getRevisions($review));

        $user = Assert::notNull($this->userRepository->find(Assert::notNull($this->userId)));

        $comment = new Comment();
        $comment->setFilePath($filepath);
        $comment->setTag(null);
        $comment->setLineReference(new LineReference($filepath, $filepath, $lineNumber, lineAfter: $lineNumber, headSha: $revision->getCommitHash()));
        $comment->setReview($review);
        $comment->setMessage($message);
        $comment->setUser($user);
        $comment->setNotificationStatus(NotificationStatus::all());
        $comment->setCreateTimestamp($this->now()->getTimestamp());
        $comment->setUpdateTimestamp($this->now()->getTimestamp());

        $review->getComments()->add($comment);
        $this->commentRepository->save($comment, true);

        return 'Comment added successfully.';
    }
}
