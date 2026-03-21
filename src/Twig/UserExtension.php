<?php
declare(strict_types=1);

namespace DR\Review\Twig;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use DR\Review\Repository\User\UserRepository;
use Twig\Attribute\AsTwigFunction;

class UserExtension
{
    public function __construct(private readonly UserRepository $userRepository)
    {
    }

    /**
     * @throws NonUniqueResultException|NoResultException
     */
    #[AsTwigFunction(name: 'new_user_count')]
    public function getUserCount(): int
    {
        return $this->userRepository->getNewUserCount();
    }
}
