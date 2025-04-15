<?php
declare(strict_types=1);

namespace DR\Review\Service\User;

use DR\Review\Entity\User\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UserEntityProvider
{
    public function __construct(private readonly TokenStorageInterface $tokenStore)
    {
    }

    public function getUser(): ?User
    {
        $user = $this->tokenStore->getToken()?->getUser();

        return $user instanceof User ? $user : null;
    }
}
