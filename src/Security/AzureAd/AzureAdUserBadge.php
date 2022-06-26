<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Security\AzureAd;

use Doctrine\Persistence\ManagerRegistry;
use DR\GitCommitNotification\Entity\User;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;

class AzureAdUserBadge extends UserBadge
{
    public function __construct(private ManagerRegistry $doctrine, private string $email, private string $name)
    {
        parent::__construct($email, fn() => $this->fetchOrCreateUser());
    }

    private function fetchOrCreateUser(): ?User
    {
        // fetch user for name (email), or create when non-existent.
        $user = $this->doctrine->getRepository(User::class)->findOneBy(['email' => $this->email]);

        // create user if not exists
        if ($user === null) {
            $this->doctrine->getRepository(User::class)->add((new User())->setEmail($this->email)->setName($this->name), true);
            $user = $this->doctrine->getRepository(User::class)->findOneBy(['email' => $this->email]);
        }

        return $user;
    }
}
