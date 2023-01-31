<?php
declare(strict_types=1);

namespace DR\Review\Service\CodeReview\Comment;

use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\UserMention;
use DR\Review\Entity\User\User;
use DR\Review\Repository\Review\UserMentionRepository;
use DR\Review\Repository\User\UserRepository;
use DR\Review\Utility\Arrays;

class CommentMentionService
{
    /** @var array<int, User>|null */
    private ?array $users = null;

    public function __construct(private readonly UserRepository $userRepository, private readonly UserMentionRepository $mentionRepository)
    {
    }

    public function updateMentions(Comment $comment): void
    {
        // fetch all user mentions from message
        $mentions = [$this->getMentionedUsers((string)$comment->getMessage())];
        foreach ($comment->getReplies() as $reply) {
            $mentions[] = $this->getMentionedUsers((string)$reply->getMessage());
        }
        $mentions = array_merge(...$mentions);

        // create new mention on comment
        $userMentions = [];
        foreach ($mentions as $user) {
            $userMentions[] = (new UserMention())->setUserId((int)$user->getId())->setComment($comment);
        }
        $this->mentionRepository->saveAll($comment, $userMentions);
    }

    /**
     * @return array<string, User>
     */
    public function getMentionedUsers(string $message): array
    {
        $matchCount = preg_match_all('/@user:(\d+)\[.*?]/', $message, $matches);
        if ($matchCount === 0) {
            return [];
        }

        if ($this->users === null) {
            $this->users = Arrays::mapAssoc($this->userRepository->findAll(), static fn(User $user) => [(int)$user->getId(), $user]);
        }

        // match @mention to user
        $mentions = [];
        for ($i = 0; $i < $matchCount; $i++) {
            $userId = (int)$matches[1][$i];
            $user   = $this->users[$userId] ?? null;
            if ($user === null) {
                continue;
            }

            $mentions[(string)$matches[0][$i]] = $user;
        }

        return $mentions;
    }

    /**
     * @param array<string, User> $users
     */
    public function replaceMentionedUsers(string $message, array $users): string
    {
        foreach ($users as $match => $user) {
            $mention = sprintf('[@%s](mailto:%s)', $user->getName(), $user->getEmail());
            $message = str_replace($match, $mention, $message);
        }

        return $message;
    }
}
