<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Entity\Notification;

use DateTimeImmutable;
use DR\Review\Entity\Notification\Frequency;
use DR\Review\Tests\AbstractTestCase;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(Frequency::class)]
class FrequencyTest extends AbstractTestCase
{
    public function testIsValid(): void
    {
        static::assertTrue(Frequency::isValid(Frequency::ONCE_PER_HOUR));
        static::assertTrue(Frequency::isValid(Frequency::ONCE_PER_TWO_HOURS));
        static::assertTrue(Frequency::isValid(Frequency::ONCE_PER_THREE_HOURS));
        static::assertTrue(Frequency::isValid(Frequency::ONCE_PER_FOUR_HOURS));
        static::assertTrue(Frequency::isValid(Frequency::ONCE_PER_DAY));
        static::assertTrue(Frequency::isValid(Frequency::ONCE_PER_WEEK));
        static::assertFalse(Frequency::isValid('foobar'));
    }

    #[DataProvider('getPeriodDataProvider')]
    public static function testGetPeriod(string $frequency, DateTimeImmutable $expectedStartTime): void
    {
        $currentTime = new DateTimeImmutable('2021-10-18 22:05:00');
        $period      = Frequency::getPeriod($currentTime, $frequency);

        static::assertEquals($expectedStartTime, $period->getStartDate());
        static::assertEquals($currentTime, $period->getEndDate());
    }

    /**
     * @return array<string, array<string|DateTimeImmutable>>
     */
    public static function getPeriodDataProvider(): array
    {
        return [
            'ONCE_PER_HOUR'        => [Frequency::ONCE_PER_HOUR, new DateTimeImmutable('2021-10-18 21:05:00')],
            'ONCE_PER_TWO_HOURS'   => [Frequency::ONCE_PER_TWO_HOURS, new DateTimeImmutable('2021-10-18 20:05:00')],
            'ONCE_PER_THREE_HOURS' => [Frequency::ONCE_PER_THREE_HOURS, new DateTimeImmutable('2021-10-18 19:05:00')],
            'ONCE_PER_FOUR_HOURS'  => [Frequency::ONCE_PER_FOUR_HOURS, new DateTimeImmutable('2021-10-18 18:05:00')],
            'ONCE_PER_DAY'         => [Frequency::ONCE_PER_DAY, new DateTimeImmutable('2021-10-17 22:05:00')],
            'ONCE_PER_WEEK'        => [Frequency::ONCE_PER_WEEK, new DateTimeImmutable('2021-10-11 22:05:00')],
        ];
    }

    public function testToGetPeriodInvalidArgument(): void
    {
        $currentTime = new DateTimeImmutable('2021-10-18 22:05:00');
        $this->expectException(InvalidArgumentException::class);
        Frequency::getPeriod($currentTime, 'foobar');
    }
}
