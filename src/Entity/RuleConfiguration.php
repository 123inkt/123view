<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Entity;

use DateTimeInterface;

class RuleConfiguration
{
    public function __construct(public DateTimeInterface $startTime, public DateTimeInterface $endTime, public Rule $rule)
    {
    }
}
