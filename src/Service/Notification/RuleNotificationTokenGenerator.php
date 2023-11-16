<?php
declare(strict_types=1);

namespace DR\Review\Service\Notification;

use DR\Review\Entity\Notification\RuleNotification;

class RuleNotificationTokenGenerator
{
    public function __construct(private readonly string $appSecret)
    {
    }

    public function generate(RuleNotification $notification): string
    {
        $notificationId  = $notification->getId();
        $notifyTimestamp = $notification->getNotifyTimestamp();
        $createTimestamp = $notification->getCreateTimestamp();
        $userId          = $notification->getRule()->getUser()->getId();

        $string = sprintf(
            '%s%s%s%s%s%s%s%s',
            $this->appSecret,
            $notificationId,
            $this->appSecret,
            $notifyTimestamp,
            $createTimestamp,
            $this->appSecret,
            $userId,
            $this->appSecret
        );

        return hash('sha512', $string);
    }
}
