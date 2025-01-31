<?php
declare(strict_types=1);

namespace DR\Review\Security\AzureAd;

use DR\Review\Entity\User\User;
use DR\Review\Repository\User\UserRepository;
use DR\Review\Security\Role\Roles;
use DR\Utils\Assert;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;

class AzureAdUserBadgeFactory
{
    public function __construct(private UserRepository $userRepository)
    {
    }

    public function create(string $email, string $name): UserBadge
    {
        return new UserBadge(
            $email,
            function () use ($email, $name) {
                // fetch user for name (email), or create when non-existent.
                $user = $this->userRepository->findOneBy(['email' => $email]);

                // create user if not exists
                if ($user !== null) {
                    return $user;
                }
                $user = (new User())->setEmail(Assert::nonEmptyString($email))->setName($name);

                // make first user admin
                if ($this->userRepository->getUserCount() === 0) {
                    $user->setRoles([Roles::ROLE_USER, Roles::ROLE_ADMIN]);
                }

                $this->userRepository->save($user, true);

                return $this->userRepository->findOneBy(['email' => $email]);
            }
        );
    }
}
