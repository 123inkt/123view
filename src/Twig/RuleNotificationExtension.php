<?php
declare(strict_types=1);

namespace DR\Review\Twig;

use Doctrine\ORM\Exception\ORMException;
use DR\Review\Repository\Config\RuleNotificationRepository;
use DR\Review\Service\User\UserEntityProvider;
use Twig\Attribute\AsTwigFunction;

class RuleNotificationExtension
{
    private ?int $notificationCount = null;

    public function __construct(
        private readonly UserEntityProvider $userProvider,
        private readonly RuleNotificationRepository $notificationRepository
    ) {
    }

    /**
     * @throws ORMException
     */
    #[AsTwigFunction(name: 'rule_notification_count')]
    public function getNotificationCount(): int
    {
        if ($this->notificationCount !== null) {
            return $this->notificationCount;
        }

        $user = $this->userProvider->getUser();
        if ($user === null) {
            return $this->notificationCount = 0;
        }

        return $this->notificationCount = $this->notificationRepository->getUnreadNotificationCountForUser($user);
    }
}
