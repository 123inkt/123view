<?php
declare(strict_types=1);

namespace DR\Review\ViewModelProvider;

use DR\Review\Entity\Notification\RuleNotificationReadEnum;
use DR\Review\Entity\User\User;
use DR\Review\Repository\Config\RuleNotificationRepository;
use DR\Review\ViewModel\App\Notification\RuleNotificationViewModel;

class RuleNotificationViewModelProvider
{
    public function __construct(private readonly ?User $user, private readonly RuleNotificationRepository $notificationRepository)
    {
    }

    public function getNotificationsViewModel(?RuleNotificationReadEnum $filter): RuleNotificationViewModel
    {
        $notifications = $this->user === null ? [] : $this->notificationRepository->getNotificationsForUser($this->user, $filter);

        return new RuleNotificationViewModel($notifications);
    }
}
