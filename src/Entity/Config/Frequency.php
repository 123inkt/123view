<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Entity\Config;

use DateInterval;
use DatePeriod;
use DateTime;
use DateTimeImmutable;
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

    public static function getPeriod(DateTimeImmutable $currentTime, string $frequency): DatePeriod
    {
        $interval = match ($frequency) {
            self::ONCE_PER_HOUR => new DateInterval("PT1H"),
            self::ONCE_PER_TWO_HOURS => new DateInterval("PT2H"),
            self::ONCE_PER_THREE_HOURS => new DateInterval("PT3H"),
            self::ONCE_PER_FOUR_HOURS => new DateInterval("PT4H"),
            self::ONCE_PER_DAY => new DateInterval("P1D"),
            self::ONCE_PER_WEEK => new DateInterval("P7D"),
            default => throw new InvalidArgumentException('Invalid frequency: ' . $frequency),
        };

        return new DatePeriod(DateTime::createFromImmutable($currentTime)->sub($interval), $interval, $currentTime);
    }
}
