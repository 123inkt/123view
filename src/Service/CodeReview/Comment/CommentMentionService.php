<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service\CodeReview\Comment;

use DR\GitCommitNotification\Entity\User\User;
use DR\GitCommitNotification\Repository\Config\UserRepository;
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
}
