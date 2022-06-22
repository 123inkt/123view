<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Entity;

use DateTimeInterface;

class RuleConfiguration
{
    /**
     * @param ExternalLink[] $externalLinks
     */
    public function __construct(
        public DateTimeInterface $startTime,
        public DateTimeInterface $endTime,
        public array $externalLinks,
        public Rule $rule
    ) {
    }
}
