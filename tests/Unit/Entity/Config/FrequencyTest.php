<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Entity\Config;

use DR\GitCommitNotification\Entity\Config\Frequency;
use DR\GitCommitNotification\Tests\AbstractTest;
use InvalidArgumentException;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Entity\Config\Frequency
 */
class FrequencyTest extends AbstractTest
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
     * @covers ::toSince
     */
    public function testToSince(): void
    {
        static::assertSame('1 hour ago', Frequency::toSince(Frequency::ONCE_PER_HOUR));
        static::assertSame('2 hours ago', Frequency::toSince(Frequency::ONCE_PER_TWO_HOURS));
        static::assertSame('3 hours ago', Frequency::toSince(Frequency::ONCE_PER_THREE_HOURS));
        static::assertSame('4 hours ago', Frequency::toSince(Frequency::ONCE_PER_FOUR_HOURS));
        static::assertSame('1 day ago', Frequency::toSince(Frequency::ONCE_PER_DAY));
        static::assertSame('1 week ago', Frequency::toSince(Frequency::ONCE_PER_WEEK));
    }

    /**
     * @covers ::toSince
     */
    public function testToSinceInvalidArgument(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Frequency::toSince('foobar');
    }
}
