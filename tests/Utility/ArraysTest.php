<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Utility;

use DR\GitCommitNotification\Tests\AbstractTestCase;
use DR\GitCommitNotification\Utility\Arrays;
use RuntimeException;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Utility\Arrays
 * @covers ::__construct
 */
class ArraysTest extends AbstractTestCase
{
    /**
     * @covers ::first
     */
    public function testFirstThrowsExceptionOnEmptyArray(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unable to obtain first item from array');
        Arrays::first([]);
    }

    /**
     * @covers ::first
     */
    public function testFirst(): void
    {
        static::assertSame('foo', Arrays::first(['foo', 'bar']));
    }
}
