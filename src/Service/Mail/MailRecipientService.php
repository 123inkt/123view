<?php
declare(strict_types=1);

namespace DR\Review\Service\Mail;

use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\CommentReply;
use DR\Review\Entity\User\User;
use DR\Review\Exception\RepositoryException;
use DR\Review\Service\CodeReview\CodeReviewRevisionService;
use DR\Review\Service\CodeReview\Comment\CommentMentionService;
use DR\Review\Service\User\UserService;
use DR\Utils\Arrays;

class MailRecipientService
{
    public function __construct(
        private readonly UserService $userService,
        private readonly CommentMentionService $mentionService,
        private readonly CodeReviewRevisionService $revisionService,
    ) {
    }

    /**
     * @return User[]
     * @throws RepositoryException
     */
    public function getUsersForReview(CodeReview $review): array
    {
        $revisions = $this->revisionService->getRevisions($review);
        $users     = $this->userService->getUsersForRevisions($revisions);
        foreach ($review->getReviewers() as $reviewer) {
            $users[] = $reviewer->getUser();
        }

        return Arrays::unique($users);
    }

    /**
     * @return User[]
     */
    public function getUserForComment(Comment $comment): array
    {
        return array_merge(
            [$comment->getUser()],
            array_values($this->mentionService->getMentionedUsers($comment->getMessage()))
        );
    }

    /**
     * @return User[]
     */
    public function getUsersForReply(Comment $comment, ?CommentReply $commentReply = null): array
    {
        $subscribers = [];
        foreach ($comment->getReplies() as $reply) {
            $subscribers[] = $reply->getUser();

            foreach ($this->mentionService->getMentionedUsers($reply->getMessage()) as $user) {
                $subscribers[] = $user;
            }

            if ($reply === $commentReply) {
                break;
            }
        }

        return $subscribers;
    }
}
