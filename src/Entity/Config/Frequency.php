<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Entity\Config;

use DateInterval;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
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

    /**
     * @return array{DateTimeInterface, DateTimeInterface}
     */
    public static function getPeriod(DateTimeImmutable $currentTime, string $frequency): array
    {
        switch ($frequency) {
            case self::ONCE_PER_HOUR:
                $interval = new DateInterval("PT1H");
                break;
            case self::ONCE_PER_TWO_HOURS:
                $interval = new DateInterval("PT2H");
                break;
            case self::ONCE_PER_THREE_HOURS:
                $interval = new DateInterval("PT3H");
                break;
            case self::ONCE_PER_FOUR_HOURS:
                $interval = new DateInterval("PT4H");
                break;
            case self::ONCE_PER_DAY:
                $interval = new DateInterval("P1D");
                break;
            case self::ONCE_PER_WEEK:
                $interval = new DateInterval("P7D");
                break;
            default:
                throw new InvalidArgumentException('Invalid frequency: ' . $frequency);
        }

        return [DateTime::createFromImmutable($currentTime)->sub($interval), $currentTime];
    }
}
