<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Utility;

use DR\GitCommitNotification\Entity\Config\Rule;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use DR\GitCommitNotification\Utility\Assert;
use RuntimeException;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Utility\Assert
 */
class TypeTest extends AbstractTestCase
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
