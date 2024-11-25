<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use DR\Review\Doctrine\Type\AbstractEnumType;
use DR\Review\Tests\AbstractTestCase;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(AbstractEnumType::class)]
class AbstractEnumTypeTest extends AbstractTestCase
{
    private AbstractEnumType $enumType;

    protected function setUp(): void
    {
        parent::setUp();
        $this->enumType = new class extends AbstractEnumType {
            public const string   TYPE   = 'type';
            protected const array VALUES = ['foo', 'bar'];
        };
    }

    public function testGetSQLDeclaration(): void
    {
        $result = $this->enumType->getSQLDeclaration([], $this->createMock(AbstractPlatform::class));
        static::assertSame("ENUM('foo', 'bar')", $result);
    }

    /**
     * @throws ConversionException
     */
    public function testConvertToDatabaseValue(): void
    {
        static::assertNull($this->enumType->convertToDatabaseValue(null, $this->createMock(AbstractPlatform::class)));
        static::assertSame('foo', $this->enumType->convertToDatabaseValue('foo', $this->createMock(AbstractPlatform::class)));
    }

    /**
     * @throws ConversionException
     */
    public function testConvertToDatabaseValueThrowsExceptionOnInvalidArgument(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid value 'foobar' for type 'type'");
        $this->enumType->convertToDatabaseValue('foobar', $this->createMock(AbstractPlatform::class));
    }

    public function testGetName(): void
    {
        static::assertSame('type', $this->enumType->getName());
    }
}
