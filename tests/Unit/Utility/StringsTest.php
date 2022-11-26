<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Utility;

use DR\GitCommitNotification\Tests\AbstractTestCase;
use DR\GitCommitNotification\Utility\Strings;
use InvalidArgumentException;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Utility\Strings
 */
class StringsTest extends AbstractTestCase
{
    /**
     * @covers ::findPrefix
     */
    public function testFindPrefix(): void
    {
        static::assertSame('', Strings::findPrefix('a', 'b'));
        static::assertSame('', Strings::findPrefix('a', 'bb'));
        static::assertSame('', Strings::findPrefix('aa', 'b'));
        static::assertSame('', Strings::findPrefix('aa', ''));
        static::assertSame('', Strings::findPrefix('', 'bb'));
        static::assertSame('', Strings::findPrefix('abcd', 'efgh'));

        static::assertSame('a', Strings::findPrefix('aa', 'a'));
        static::assertSame('a', Strings::findPrefix('a', 'aa'));
        static::assertSame('ab', Strings::findPrefix('abc', 'abd'));
    }

    /**
     * @covers ::findSuffix
     */
    public function testFindSuffix(): void
    {
        static::assertSame('', Strings::findSuffix('a', 'b'));
        static::assertSame('', Strings::findSuffix('a', 'bb'));
        static::assertSame('', Strings::findSuffix('aa', 'b'));
        static::assertSame('', Strings::findSuffix('aa', ''));
        static::assertSame('', Strings::findSuffix('', 'bb'));
        static::assertSame('', Strings::findSuffix('abcd', 'efgh'));

        static::assertSame('a', Strings::findSuffix('aa', 'a'));
        static::assertSame('a', Strings::findSuffix('a', 'aa'));
        static::assertSame('bc', Strings::findSuffix('abc', 'dbc'));
    }

    /**
     * @covers ::replace
     */
    public function testReplace(): void
    {
        static::assertSame('', Strings::replace("foobar", "foo", "bar"));
        static::assertSame('bar', Strings::replace("foobar", "foo", ""));
        static::assertSame('foo', Strings::replace("foobar", "", "bar"));
    }

    /**
     * @covers ::replacePrefix
     */
    public function testReplacePrefix(): void
    {
        static::assertSame("bar", Strings::replacePrefix("foobar", "foo"));
        static::assertSame("foobar", Strings::replacePrefix("foobar", "bar"));
        static::assertSame("foobar", Strings::replacePrefix("foobar", ""));
    }

    /**
     * @covers ::replaceSuffix
     */
    public function testReplaceSuffix(): void
    {
        static::assertSame("foo", Strings::replaceSuffix("foobar", "bar"));
        static::assertSame("foobar", Strings::replaceSuffix("foobar", "foo"));
        static::assertSame("foobar", Strings::replaceSuffix("foobar", ""));
    }

    /**
     * @covers ::string
     */
    public function testString(): void
    {
        static::assertSame('foobar', Strings::string('foobar'));
    }

    /**
     * @covers ::string
     */
    public function testStringNonStringValue(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expecting value to be `string`, received');
        static::assertSame('foobar', Strings::string(123));
    }
}
