<?php
declare(strict_types=1);

namespace DR\Review\Entity\Notification;

use DatePeriod;
use DateTimeImmutable;

class RuleConfiguration
{
    /**
     * @param DatePeriod<DateTimeImmutable, DateTimeImmutable> $period
     */
    public function __construct(public readonly DatePeriod $period, public readonly Rule $rule)
    {
    }
}
