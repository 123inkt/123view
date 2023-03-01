<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Utility;

use DR\Review\Tests\AbstractTestCase;
use DR\Review\Utility\Assert;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use stdClass;

/**
 * @coversDefaultClass \DR\Review\Utility\Assert
 */
class AssertTest extends AbstractTestCase
{
    /**
     * @covers ::notNull
     */
    public function testNotNullFailure(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Expecting value to be not null');
        Assert::notNull(null);
    }

    /**
     * @covers ::notNull
     */
    public function testNotNullSuccess(): void
    {
        $rule = new stdClass();
        static::assertSame($rule, Assert::notNull($rule));
    }

    /**
     * @covers ::isArray
     */
    public function testIsArrayFailure(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Expecting value to be an array');
        Assert::isArray('foobar'); // @phpstan-ignore-line
    }

    /**
     * @covers ::isArray
     */
    public function testIsArray(): void
    {
        $rules = [new stdClass()];
        static::assertSame($rules, Assert::isArray($rules));
    }

    /**
     * @covers ::isCallable
     */
    public function testIsCallable(): void
    {
        $callable = [$this, 'testIsCallable'];
        static::assertSame($callable, Assert::isCallable($callable));
    }

    /**
     * @covers ::isCallable
     */
    public function testIsCallableFailure(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Expecting value to be `callable');
        Assert::isCallable('string');
    }

    /**
     * @covers ::isInt
     */
    public function testIsInt(): void
    {
        static::assertSame(5, Assert::isInt(5));
    }

    /**
     * @covers ::isInt
     */
    public function testIsIntFailure(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Expecting value to be an int');
        Assert::isInt('string'); // @phpstan-ignore-line
    }

    /**
     * @covers ::isString
     */
    public function testIsStringFailure(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Expecting value to be a string');
        Assert::isString(123); // @phpstan-ignore-line
    }

    /**
     * @covers ::isString
     */
    public function testIsString(): void
    {
        static::assertSame('string', Assert::isString('string'));
    }

    /**
     * @covers ::notFalse
     */
    public function testNotFalseFailure(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Expecting value to be not false');
        Assert::notFalse(false);
    }

    /**
     * @covers ::notFalse
     */
    public function testNotFalseSuccess(): void
    {
        $rule = new stdClass();
        static::assertSame($rule, Assert::notFalse($rule));
    }

    /**
     * @covers ::instanceOf
     */
    public function testInstanceOfFailure(): void
    {
        $object = new stdClass();
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Expecting value to be instance of RuntimeException');
        Assert::instanceOf(RuntimeException::class, $object); // @phpstan-ignore-line
    }

    /**
     * @covers ::instanceOf
     */
    public function testInstanceOfSuccess(): void
    {
        static::assertSame($this, Assert::instanceOf(self::class, $this));
        static::assertSame($this, Assert::instanceOf(AbstractTestCase::class, $this));
        static::assertSame($this, Assert::instanceOf(TestCase::class, $this));
    }
}
