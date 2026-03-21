<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Twig;

use DR\Review\Tests\AbstractTestCase;
use DR\Review\Twig\StrPadExtension;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(StrPadExtension::class)]
class StrPadExtensionTest extends AbstractTestCase
{
    public function testStrPad(): void
    {
        $extension = new StrPadExtension();
        static::assertSame('&nbsp;&nbsp;5', $extension->strpad('5', 3));
        static::assertSame('5&nbsp;&nbsp;', $extension->strpad('5', 3, 'right'));
        static::assertSame('&nbsp;5&nbsp;', $extension->strpad('5', 3, 'both'));

        // with html entity
        static::assertSame('&nbsp;&amp;&nbsp;', $extension->strpad('&', 3, 'both'));
    }

    public function testStrPadInvalidArgument(): void
    {
        $extension = new StrPadExtension();
        $this->expectException(InvalidArgumentException::class);
        $extension->strpad('5', 3, 'foobar');
    }
}
