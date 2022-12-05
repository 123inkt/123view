<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use DR\Review\Doctrine\Type\SpaceSeparatedStringValueType;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @coversDefaultClass \DR\Review\Doctrine\Type\SpaceSeparatedStringValueType
 */
class SpaceSeparatedStringValueTypeTest extends AbstractTestCase
{
    private AbstractPlatform&MockObject   $platform;
    private SpaceSeparatedStringValueType $type;

    public function setUp(): void
    {
        parent::setUp();
        $this->platform = $this->createMock(AbstractPlatform::class);
        $this->type     = new SpaceSeparatedStringValueType();
    }

    /**
     * @covers ::getSQLDeclaration
     */
    public function testGetSQLDeclaration(): void
    {
        $column = ['length' => 100];

        $this->platform->expects(self::once())->method('getStringTypeDeclarationSQL')->with($column)->willReturn('varchar');

        static::assertSame('varchar', $this->type->getSQLDeclaration($column, $this->platform));
    }

    /**
     * @covers ::getName
     */
    public function testGetName(): void
    {
        static::assertSame(SpaceSeparatedStringValueType::TYPE, $this->type->getName());
    }

    /**
     * @covers ::convertToDatabaseValue
     * @throws ConversionException
     */
    public function testConvertToDatabaseValue(): void
    {
        static::assertSame('foo bar', $this->type->convertToDatabaseValue(['foo', 'bar'], $this->platform));
    }

    /**
     * @covers ::convertToPHPValue
     * @throws ConversionException
     */
    public function testConvertToPHPValue(): void
    {
        $result = $this->type->convertToPHPValue(' foo bar', $this->platform);
        static::assertNotNull($result);
        static::assertSame(['foo', 'bar'], array_values($result));
    }
}
