<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Twig;

use DR\GitCommitNotification\Tests\AbstractTestCase;
use DR\GitCommitNotification\Twig\StrPadExtension;
use InvalidArgumentException;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Twig\StrPadExtension
 */
class StrPadExtensionTest extends AbstractTestCase
{
    /**
     * @covers ::getFilters
     */
    public function testGetFilters(): void
    {
        $extension = new StrPadExtension();
        static::assertCount(1, $extension->getFilters());
    }

    /**
     * @covers ::strpad
     */
    public function testStrPad(): void
    {
        $extension = new StrPadExtension();
        static::assertSame('&nbsp;&nbsp;5', $extension->strpad('5', 3));
        static::assertSame('5&nbsp;&nbsp;', $extension->strpad('5', 3, 'right'));
        static::assertSame('&nbsp;5&nbsp;', $extension->strpad('5', 3, 'both'));

        // with html entity
        static::assertSame('&nbsp;&amp;&nbsp;', $extension->strpad('&', 3, 'both'));
    }

    /**
     * @covers ::strpad
     */
    public function testStrPadInvalidArgument(): void
    {
        $extension = new StrPadExtension();
        $this->expectException(InvalidArgumentException::class);
        $extension->strpad('5', 3, 'foobar');
    }
}
