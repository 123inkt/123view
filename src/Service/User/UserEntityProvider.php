<?php
declare(strict_types=1);

namespace DR\Review\Service\User;

use DR\Review\Entity\User\User;
use DR\Utils\Assert;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

readonly class UserEntityProvider
{
    public function __construct(private TokenStorageInterface $tokenStore, private Security $security)
    {
    }

    public function getCurrentUser(): User
    {
        return Assert::isInstanceOf($this->security->getUser(), User::class);
    }

    public function getUser(): ?User
    {
        $user = $this->tokenStore->getToken()?->getUser();

        return $user instanceof User ? $user : null;
    }
}
