<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Entity\Notification;

use DatePeriod;

class RuleConfiguration
{
    public function __construct(public readonly DatePeriod $period, public readonly Rule $rule)
    {
    }
}
