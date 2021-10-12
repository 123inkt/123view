<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Entity\Config;

use InvalidArgumentException;

class Frequency
{
    public const ONCE_PER_HOUR        = 'once-per-hour';
    public const ONCE_PER_TWO_HOURS   = 'once-per-two-hours';
    public const ONCE_PER_THREE_HOURS = 'once-per-three-hours';
    public const ONCE_PER_FOUR_HOURS  = 'once-per-four-hours';
    public const ONCE_PER_DAY         = 'once-per-day';
    public const ONCE_PER_WEEK        = 'once-per-week';

    public static function isValid(?string $frequency): bool
    {
        $valid = [
            self::ONCE_PER_HOUR,
            self::ONCE_PER_TWO_HOURS,
            self::ONCE_PER_THREE_HOURS,
            self::ONCE_PER_FOUR_HOURS,
            self::ONCE_PER_DAY,
            self::ONCE_PER_WEEK
        ];
        return in_array($frequency, $valid, true);
    }

    public static function toSince(string $frequency): string
    {
        switch ($frequency) {
            case self::ONCE_PER_HOUR:
                return '1 hour ago';
            case self::ONCE_PER_TWO_HOURS:
                return '2 hours ago';
            case self::ONCE_PER_THREE_HOURS:
                return '3 hours ago';
            case self::ONCE_PER_FOUR_HOURS:
                return '4 hours ago';
            case self::ONCE_PER_DAY:
                return '1 day ago';
            case self::ONCE_PER_WEEK:
                return '1 week ago';
        }
        throw new InvalidArgumentException('Invalid frequency: ' . $frequency);
    }
}
