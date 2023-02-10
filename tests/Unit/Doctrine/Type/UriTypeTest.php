<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use DR\Review\Doctrine\Type\UriType;
use DR\Review\Tests\AbstractTestCase;
use League\Uri\Uri;
use PHPUnit\Framework\MockObject\MockObject;
use stdClass;

/**
 * @coversDefaultClass \DR\Review\Doctrine\Type\UriType
 * @covers ::__construct
 */
class UriTypeTest extends AbstractTestCase
{
    private AbstractPlatform&MockObject $platform;
    private UriType                     $type;

    public function setUp(): void
    {
        parent::setUp();
        $this->platform = $this->createMock(AbstractPlatform::class);
        $this->type     = new UriType();
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
        static::assertSame(UriType::TYPE, $this->type->getName());
    }

    /**
     * @covers ::convertToDatabaseValue
     * @throws ConversionException
     */
    public function testConvertToDatabaseValueFailure(): void
    {
        $this->expectException(ConversionException::class);
        $this->expectExceptionMessage('Could not convert PHP value');
        static::assertNull($this->type->convertToDatabaseValue('foobar', $this->platform));
    }

    /**
     * @covers ::convertToDatabaseValue
     * @throws ConversionException
     */
    public function testConvertToDatabaseValue(): void
    {
        static::assertNull($this->type->convertToDatabaseValue(null, $this->platform));
        static::assertSame(
            'https://example.com/',
            $this->type->convertToDatabaseValue(Uri::createFromString('https://example.com/'), $this->platform)
        );
    }

    /**
     * @covers ::convertToPHPValue
     * @throws ConversionException
     */
    public function testConvertToPHPValueFailure(): void
    {
        $this->expectException(ConversionException::class);
        $this->expectExceptionMessage('Could not convert PHP value');
        static::assertNull($this->type->convertToPHPValue(new stdClass(), $this->platform));
    }

    /**
     * @covers ::convertToPHPValue
     * @throws ConversionException
     */
    public function testConvertToPHPValue(): void
    {
        static::assertNull($this->type->convertToPHPValue(null, $this->platform));
        static::assertEquals(Uri::createFromString('https://example.com/'), $this->type->convertToPHPValue('https://example.com/', $this->platform));
    }
}
