<?php
declare(strict_types=1);

namespace DR\Review\Service\Notification;

use DatePeriod;
use DR\Review\Entity\Notification\Rule;
use DR\Review\Entity\Notification\RuleNotification;
use DR\Review\Repository\Config\RuleNotificationRepository;
use DR\Utils\Assert;
use Symfony\Component\Clock\ClockAwareTrait;

class RuleNotificationService
{
    use ClockAwareTrait;

    public function __construct(private readonly RuleNotificationRepository $repository)
    {
    }

    public function addRuleNotification(Rule $rule, DatePeriod $period): RuleNotification
    {
        $notification = new RuleNotification();
        $notification->setRule($rule);
        $notification->setNotifyTimestamp(Assert::notNull($period->end)->getTimestamp());
        $notification->setCreateTimestamp($this->now()->getTimestamp());
        $this->repository->save($notification, true);

        return $notification;
    }
}
