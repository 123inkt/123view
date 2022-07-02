<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Security\AzureAd;

use DR\GitCommitNotification\Entity\Config\User;
use DR\GitCommitNotification\Repository\Config\UserRepository;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;

class AzureAdUserBadgeFactory
{
    public function __construct(private UserRepository $userRepository)
    {
    }

    public function create(string $email, string $name): UserBadge
    {
        return new UserBadge($email, function () use ($email, $name) {
            // fetch user for name (email), or create when non-existent.
            $user = $this->userRepository->findOneBy(['email' => $email]);

            // create user if not exists
            if ($user === null) {
                $this->userRepository->add((new User())->setEmail($email)->setName($name), true);
                $user = $this->userRepository->findOneBy(['email' => $email]);
            }

            return $user;
        });
    }
}
