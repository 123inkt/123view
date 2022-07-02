<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Doctrine\Type;

use DateInterval;
use DateTime;
use DateTimeImmutable;
use DR\GitCommitNotification\Doctrine\Type\FrequencyType;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use InvalidArgumentException;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Doctrine\Type\FrequencyType
 */
class FrequencyTypeTest extends AbstractTestCase
{
    /**
     * @covers ::isValid
     */
    public function testIsValid(): void
    {
        static::assertTrue(FrequencyType::isValid('once-per-hour'));
        static::assertFalse(FrequencyType::isValid('foobar'));
    }

    /**
     * @covers ::getPeriod
     */
    public function testGetPeriodInvalidFrequency(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid frequency: foobar');
        FrequencyType::getPeriod(new DateTimeImmutable(), 'foobar');
    }

    /**
     * @covers ::getPeriod
     * @dataProvider dataProvider
     */
    public function testGetPeriod(string $frequency, DateInterval $interval): void
    {
        $currentTime = new DateTimeImmutable();
        $startTime   = DateTime::createFromImmutable($currentTime)->sub($interval);
        [$actualStartTime, $actualEndTime] = FrequencyType::getPeriod(new DateTimeImmutable(), $frequency);

        // format to string to compare
        static::assertSame($startTime->format('Y-m-d H:i'), $actualStartTime->format('Y-m-d H:i'));
        static::assertSame($currentTime->format('Y-m-d H:i'), $actualEndTime->format('Y-m-d H:i'));
    }

    /**
     * @return array<string, array<string|DateInterval>>
     */
    public function dataProvider(): array
    {
        return [
            FrequencyType::ONCE_PER_HOUR        => [FrequencyType::ONCE_PER_HOUR, new DateInterval("PT1H")],
            FrequencyType::ONCE_PER_TWO_HOURS   => [FrequencyType::ONCE_PER_TWO_HOURS, new DateInterval("PT2H")],
            FrequencyType::ONCE_PER_THREE_HOURS => [FrequencyType::ONCE_PER_THREE_HOURS, new DateInterval("PT3H")],
            FrequencyType::ONCE_PER_FOUR_HOURS  => [FrequencyType::ONCE_PER_FOUR_HOURS, new DateInterval("PT4H")],
            FrequencyType::ONCE_PER_DAY         => [FrequencyType::ONCE_PER_DAY, new DateInterval("P1D")],
            FrequencyType::ONCE_PER_WEEK        => [FrequencyType::ONCE_PER_WEEK, new DateInterval("P7D")]
        ];
    }
}
