<?php
declare(strict_types=1);

namespace DR\Review\Service\Ai;

use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\NotificationStatus;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Entity\User\User;
use DR\Review\Exception\Ai\CodeReviewNotFoundException;
use DR\Review\Repository\Review\CodeReviewRepository;
use DR\Review\Repository\Review\CommentRepository;
use DR\Review\Service\Ai\Comment\AiCommentDeduplicationChecker;
use DR\Review\Service\CodeReview\CodeReviewRevisionService;
use DR\Review\Service\CodeReview\LineReferenceFactory;
use DR\Utils\Arrays;
use Psr\Log\LoggerInterface;
use Symfony\Component\Clock\ClockAwareTrait;

class AddCommentService
{
    use ClockAwareTrait;

    public function __construct(
        private ?LoggerInterface $aiLogger,
        private readonly CodeReviewRepository $repository,
        private readonly CommentRepository $commentRepository,
        private readonly CodeReviewRevisionService $reviewRevisionService,
        private readonly LineReferenceFactory $lineReferenceFactory,
        private readonly AiCommentDeduplicationChecker $deduplicationChecker,
    ) {
    }

    /**
     * @return bool false if the comment was a duplicate of an existing comment and was skipped
     */
    public function addComment(User $user, int $codeReviewId, string $filepath, int $lineNumber, string $message, ?string $codeSuggestion): bool
    {
        $review = $this->repository->find($codeReviewId);
        if ($review === null) {
            throw new CodeReviewNotFoundException($codeReviewId);
        }

        if ($codeSuggestion !== null && $codeSuggestion !== '') {
            $message .= "\n\n```\n" . $codeSuggestion . "\n```";
        }

        // markdown bold + : turn in to kiss emoticon. Replace it to bold only.
        $message = str_replace(':**', '**', $message);

        /** @var Revision $revision */
        $revision      = Arrays::last($this->reviewRevisionService->getRevisions($review));
        $lineReference = $this->lineReferenceFactory->createFromReview($review, $filepath, $lineNumber, $revision->getCommitHash());

        if ($this->deduplicationChecker->isDuplicate($review, $filepath, $lineReference, $message)) {
            $this->aiLogger?->info(
                'AddCommentService: Skipping duplicate comment on "{filepath}" at line {line} in review {id}',
                ['id' => $review->getId(), 'filepath' => $filepath, 'line' => $lineNumber]
            );

            return false;
        }

        $this->aiLogger?->info(
            'AddCommentService: Adding comment to file "{filepath}" at line {line} in review {id}',
            ['id' => $review->getId(), 'filepath' => $filepath, 'line' => $lineNumber]
        );

        $comment = new Comment();
        $comment->setFilePath($filepath);
        $comment->setTag(null);
        $comment->setLineReference($lineReference);
        $comment->setReview($review);
        $comment->setMessage($message);
        $comment->setUser($user);
        $comment->setNotificationStatus(NotificationStatus::all());
        $comment->setCreateTimestamp($this->now()->getTimestamp());
        $comment->setUpdateTimestamp($this->now()->getTimestamp());

        $review->getComments()->add($comment);
        $this->commentRepository->save($comment, true);

        return true;
    }
}
