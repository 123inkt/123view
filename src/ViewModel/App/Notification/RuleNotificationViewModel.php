<?php
declare(strict_types=1);

namespace DR\Review\ViewModel\App\Notification;

use DR\Review\Entity\Notification\RuleNotification;

class RuleNotificationViewModel
{
    /**
     * @param array<RuleNotification> $notifications
     */
    public function __construct(public readonly array $notifications)
    {
    }
}
