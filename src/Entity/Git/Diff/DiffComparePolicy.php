<?php
declare(strict_types=1);

namespace DR\Review\Entity\Git\Diff;

use DR\JBDiff\ComparisonPolicy;

enum DiffComparePolicy: string
{
    case ALL = 'all';
    case TRIM = 'trim';
    case IGNORE = 'ignore';

    public function toComparisonPolicy(): ComparisonPolicy
    {
        return match ($this) {
            self::ALL    => ComparisonPolicy::DEFAULT,
            self::TRIM   => ComparisonPolicy::TRIM_WHITESPACES,
            self::IGNORE => ComparisonPolicy::IGNORE_WHITESPACES
        };
    }
}
