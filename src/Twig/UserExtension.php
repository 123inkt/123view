<?php
declare(strict_types=1);

namespace DR\Review\Twig;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use DR\Review\Repository\User\UserRepository;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class UserExtension extends AbstractExtension
{
    public function __construct(private readonly UserRepository $userRepository)
    {
    }

    /**
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [new TwigFunction('new_user_count', [$this, 'getUserCount'])];
    }

    /**
     * @throws NonUniqueResultException|NoResultException
     */
    public function getUserCount(): int
    {
        return $this->userRepository->getNewUserCount();
    }
}
