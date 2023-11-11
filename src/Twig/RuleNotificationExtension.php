<?php
declare(strict_types=1);

namespace DR\Review\Twig;

use Doctrine\ORM\Exception\ORMException;
use DR\Review\Entity\User\User;
use DR\Review\Repository\Config\RuleNotificationRepository;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class RuleNotificationExtension extends AbstractExtension
{
    private ?int $notificationCount = null;

    public function __construct(private readonly ?User $user, private readonly RuleNotificationRepository $notificationRepository)
    {
    }

    /**
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [new TwigFunction('rule_notification_count', [$this, 'getNotificationCount'])];
    }

    /**
     * @throws ORMException
     */
    public function getNotificationCount(): int
    {
        if ($this->notificationCount !== null) {
            return $this->notificationCount;
        }

        if ($this->user === null) {
            return $this->notificationCount = 0;
        }

        return $this->notificationCount = $this->notificationRepository->getUnreadNotificationCountForUser($this->user);
    }
}
