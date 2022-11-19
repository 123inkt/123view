<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service\CodeReview\Comment;

use DR\GitCommitNotification\Entity\User\User;
use DR\GitCommitNotification\Repository\User\UserRepository;
use DR\GitCommitNotification\Utility\Arrays;

class CommentMentionService
{
    /** @var array<int, User>|null */
    private ?array $users = null;

    public function __construct(private readonly UserRepository $userRepository)
    {
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
