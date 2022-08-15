<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Utility;

use DR\GitCommitNotification\Entity\Config\Rule;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use DR\GitCommitNotification\Utility\Type;
use RuntimeException;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Utility\Type
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
        Type::notNull(null);
    }

    /**
     * @covers ::notNull
     */
    public function testNotNullSuccess(): void
    {
        $rule = new Rule();
        static::assertSame($rule, Type::notNull($rule));
    }
}
