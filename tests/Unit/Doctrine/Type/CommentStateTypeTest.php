<?php

declare(strict_types=1);

namespace DR\Review\Tests\Unit\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use DR\Review\Doctrine\Type\CommentStateType;
use DR\Review\Entity\Review\CommentStateEnum;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CommentStateType::class)]
class CommentStateTypeTest extends AbstractTestCase
{
    public function testValues(): void
    {
        static::assertSame(CommentStateEnum::values(), CommentStateType::VALUES);
    }

    public function testGetSqlDeclaration(): void
    {
        $type = new CommentStateType();

        static::assertSame("ENUM('open', 'resolved')", $type->getSQLDeclaration([], static::createStub(AbstractPlatform::class)));
    }

    public function testConvertEnumValue(): void
    {
        $type = new CommentStateType();

        static::assertSame(
            CommentStateEnum::Resolved->value,
            $type->convertToDatabaseValue(CommentStateEnum::Resolved, static::createStub(AbstractPlatform::class))
        );
    }
}
