<?php
declare(strict_types=1);

namespace DR\Review\Entity\Git\Diff;

use DR\JBDiff\ComparisonPolicy;

enum DiffComparePolicy: string
{
    case ALL = 'all';
    case TRIM = 'trim';
    case IGNORE = 'ignore';
    case IGNORE_EMPTY_LINES = 'ignore_empty_lines';

    public function toComparisonPolicy(): ComparisonPolicy
    {
        return match ($this) {
            self::ALL                              => ComparisonPolicy::DEFAULT,
            self::TRIM                             => ComparisonPolicy::TRIM_WHITESPACES,
            self::IGNORE, self::IGNORE_EMPTY_LINES => ComparisonPolicy::IGNORE_WHITESPACES,
        };
    }

    /**
     * @return string[]
     */
    public static function values(): array
    {
        $values = [];
        foreach (self::cases() as $case) {
            $values[] = $case->value;
        }

        return $values;
    }
}
