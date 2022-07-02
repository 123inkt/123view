<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use DR\GitCommitNotification\Doctrine\Type\AbstractEnumType;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use InvalidArgumentException;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Doctrine\Type\AbstractEnumType
 */
class AbstractEnumTypeTest extends AbstractTestCase
{
    private AbstractEnumType $enumType;

    protected function setUp(): void
    {
        parent::setUp();
        $this->enumType = new class extends AbstractEnumType {
            public const TYPE   = 'type';
            public const VALUES = ['foo', 'bar'];
        };
    }

    /**
     * @covers ::getSQLDeclaration
     */
    public function testGetSQLDeclaration(): void
    {
        $result = $this->enumType->getSQLDeclaration([], $this->createMock(AbstractPlatform::class));
        static::assertSame("ENUM('foo', 'bar')", $result);
    }

    /**
     * @covers ::convertToDatabaseValue
     * @throws ConversionException
     */
    public function testConvertToDatabaseValue(): void
    {
        static::assertSame('foo', $this->enumType->convertToDatabaseValue('foo', $this->createMock(AbstractPlatform::class)));
    }

    /**
     * @covers ::convertToDatabaseValue
     * @throws ConversionException
     */
    public function testConvertToDatabaseValueThrowsExceptionOnInvalidArgument(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid value 'foobar' for type 'type'");
        $this->enumType->convertToDatabaseValue('foobar', $this->createMock(AbstractPlatform::class));
    }

    /**
     * @covers ::getName
     */
    public function testGetName(): void
    {
        static::assertSame('type', $this->enumType->getName());
    }

    /**
     * @covers ::requiresSQLCommentHint
     */
    public function testRequiresSQLCommentHint(): void
    {
        static::assertTrue($this->enumType->requiresSQLCommentHint($this->createMock(AbstractPlatform::class)));
    }
}
