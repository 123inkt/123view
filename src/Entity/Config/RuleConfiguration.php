<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Entity\Config;

use DateTimeInterface;

class RuleConfiguration
{
    /**
     * @param ExternalLink[] $externalLinks
     */
    public function __construct(
        public readonly DateTimeInterface $startTime,
        public readonly DateTimeInterface $endTime,
        public readonly array $externalLinks,
        public readonly Rule $rule
    ) {
    }
}
