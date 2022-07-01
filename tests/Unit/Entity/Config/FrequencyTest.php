<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Entity\Config;

use DateTimeImmutable;
use DR\GitCommitNotification\Entity\Config\Frequency;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use InvalidArgumentException;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Entity\Config\Frequency
 */
class FrequencyTest extends AbstractTestCase
{
    /**
     * @covers ::isValid
     */
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

    /**
     * @covers ::getPeriod
     * @dataProvider getPeriodDataProvider
     */
    public function testGetPeriod(string $frequency, DateTimeImmutable $expectedStartTime): void
    {
        $currentTime = new DateTimeImmutable('2021-10-18 22:05:00');
        static::assertEquals([$expectedStartTime, $currentTime], Frequency::getPeriod($currentTime, $frequency));
    }

    /**
     * @return array<string, array<string|DateTimeImmutable>>
     */
    public function getPeriodDataProvider(): array
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

    /**
     * @covers ::getPeriod
     */
    public function testToGetPeriodInvalidArgument(): void
    {
        $currentTime = new DateTimeImmutable('2021-10-18 22:05:00');
        $this->expectException(InvalidArgumentException::class);
        Frequency::getPeriod($currentTime, 'foobar');
    }
}
