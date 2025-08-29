<?php
declare(strict_types=1);

namespace DR\Review\Service\User;

use DR\Review\Entity\Revision\Revision;
use DR\Review\Entity\User\User;
use DR\Review\Repository\User\UserRepository;
use DR\Utils\Assert;
use Symfony\Bundle\SecurityBundle\Security;

readonly class UserService
{
    public function __construct(private UserRepository $userRepository, private Security $security)
    {
    }

    public function getCurrentUser(): User
    {
        return Assert::isInstanceOf($this->security->getUser(), User::class);
    }

    /**
     * @param Revision[] $revisions
     *
     * @return User[]
     */
    public function getUsersForRevisions(array $revisions): array
    {
        $emails = [];
        foreach ($revisions as $revision) {
            $emails[] = $revision->getAuthorEmail();
        }

        if (count($emails) === 0) {
            return [];
        }

        return $this->userRepository->findBy(['email' => array_unique($emails)]);
    }
}
