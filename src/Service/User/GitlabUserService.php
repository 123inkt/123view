<?php
declare(strict_types=1);

namespace DR\Review\Service\User;

use DR\Review\Entity\User\User;
use DR\Review\Repository\User\UserRepository;
use DR\Review\Service\Api\Gitlab\GitlabApi;
use Throwable;

class GitlabUserService
{
    public function __construct(private readonly UserRepository $userRepository, private readonly GitlabApi $api)
    {
    }

    /**
     * @throws Throwable
     */
    public function getUser(int $gitlabUserId, string $gitlabName): ?User
    {
        $user = $this->userRepository->findOneBy(['gitlabUserId' => $gitlabUserId]);
        if ($user !== null) {
            return $user;
        }

        $user = $this->userRepository->findOneBy(['name' => $gitlabName]);
        if ($user !== null) {
            $this->userRepository->save($user->setGitlabUserId($gitlabUserId), true);

            return $user;
        }

        $gitlabUser = $this->api->users()->getUser($gitlabUserId);
        if ($gitlabUser === null) {
            return null;
        }

        $user = $this->userRepository->findOneBy(['email' => $gitlabUser->email]);
        if ($user !== null) {
            $this->userRepository->save($user->setGitlabUserId($gitlabUserId), true);
        }

        return $user;
    }
}
