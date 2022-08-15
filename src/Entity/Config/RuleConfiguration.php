<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Entity\Config;

use DatePeriod;

class RuleConfiguration
{
    /**
     * @param ExternalLink[] $externalLinks
     */
    public function __construct(
        public readonly DatePeriod $period,
        public readonly array $externalLinks,
        public readonly Rule $rule
    ) {
    }
}
