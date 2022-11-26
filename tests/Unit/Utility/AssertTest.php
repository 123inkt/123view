<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Utility;

use DR\GitCommitNotification\Entity\Notification\Rule;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use DR\GitCommitNotification\Utility\Assert;
use RuntimeException;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Utility\Assert
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
        $rule = new Rule();
        static::assertSame($rule, Assert::notNull($rule));
    }

    /**
     * @covers ::isArray
     */
    public function testIsArrayFailure(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Expecting value to be an array');
        Assert::isArray('foobar');
    }

    /**
     * @covers ::isArray
     */
    public function testIsArray(): void
    {
        $rules = [new Rule()];
        static::assertSame($rules, Assert::isArray($rules));
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
        Assert::isInt('string');
    }

    /**
     * @covers ::isString
     */
    public function testIsStringFailure(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Expecting value to be a string');
        Assert::isString(123);
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
        $rule = new Rule();
        static::assertSame($rule, Assert::notFalse($rule));
    }
}
