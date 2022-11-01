<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service\Mail;

use DR\GitCommitNotification\Entity\Config\User;
use DR\GitCommitNotification\Entity\Review\CodeReview;
use DR\GitCommitNotification\Entity\Review\Comment;
use DR\GitCommitNotification\Entity\Review\CommentReply;
use DR\GitCommitNotification\Entity\Review\Revision;
use DR\GitCommitNotification\Repository\Config\UserRepository;
use DR\GitCommitNotification\Utility\Type;

class MailRecipientService
{
    public function __construct(private readonly UserRepository $userRepository)
    {
    }

    /**
     * @return User[]
     */
    public function getUsersForReview(CodeReview $review): array
    {
        $users = $this->getUsersForRevisions($review->getRevisions()->toArray());
        foreach ($review->getReviewers() as $reviewer) {
            $users[] = Type::notNull($reviewer->getUser());
        }

        return array_unique($users);
    }

    /**
     * @return User[]
     */
    public function getUsersForReply(Comment $comment, ?CommentReply $commentReply = null): array
    {
        $subscribers = [];
        foreach ($comment->getReplies() as $reply) {
            $subscribers[] = Type::notNull($reply->getUser());

            if ($reply === $commentReply) {
                break;
            }
        }

        return $subscribers;
    }

    /**
     * @param Revision[] $revisions
     *
     * @return User[]
     */
    public function getUsersForRevisions(array $revisions): array
    {
        $emails = [];
        foreach ($revisions as $revision) {
            $emails[] = $revision->getAuthorEmail();
        }

        if (count($emails) === 0) {
            return [];
        }

        return $this->userRepository->findBy(['email' => array_unique($emails)]);
    }
}
