<?php
declare(strict_types=1);

namespace DR\Review\ViewModel\App\Notification;

use DR\Review\Entity\Notification\Rule;
use DR\Review\Entity\Notification\RuleNotification;

class RuleNotificationViewModel
{
    /**
     * @param array<int, int>         $notificationCount key = ruleId, value = notificationCount
     * @param Rule[]                  $rules
     * @param array<RuleNotification> $notifications
     */
    public function __construct(
        public readonly ?Rule $selectedRule,
        public readonly array $notificationCount,
        public readonly array $rules,
        public readonly array $notifications,
        public readonly bool $unread
    ) {
    }

    public function getNotificationCount(Rule $rule): int
    {
        return $this->notificationCount[$rule->getId()] ?? 0;
    }
}
