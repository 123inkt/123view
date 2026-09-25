<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use DR\Review\Doctrine\Type\UriType;
use DR\Review\Tests\AbstractTestCase;
use League\Uri\Uri;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use stdClass;

#[CoversClass(UriType::class)]
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

    public function testGetSQLDeclaration(): void
    {
        $column = ['length' => 100];

        $this->platform->expects($this->once())->method('getStringTypeDeclarationSQL')->with($column)->willReturn('varchar');

        static::assertSame('varchar', $this->type->getSQLDeclaration($column, $this->platform));
    }

    public function testGetName(): void
    {
        $this->platform->expects($this->never())->method('getStringTypeDeclarationSQL');
        static::assertSame(UriType::TYPE, $this->type->getName());
    }

    /**
     * @throws ConversionException
     */
    public function testConvertToDatabaseValueFailure(): void
    {
        $this->platform->expects($this->never())->method('getStringTypeDeclarationSQL');
        $this->expectException(ConversionException::class);
        $this->expectExceptionMessage('Could not convert database value');
        $this->type->convertToDatabaseValue('foobar', $this->platform);
    }

    /**
     * @throws ConversionException
     */
    public function testConvertToDatabaseValue(): void
    {
        $this->platform->expects($this->never())->method('getStringTypeDeclarationSQL');
        static::assertNull($this->type->convertToDatabaseValue(null, $this->platform));
        static::assertSame(
            'https://example.com/',
            $this->type->convertToDatabaseValue(Uri::new('https://example.com/'), $this->platform)
        );
    }

    /**
     * @throws ConversionException
     */
    public function testConvertToPHPValueFailure(): void
    {
        $this->platform->expects($this->never())->method('getStringTypeDeclarationSQL');
        $this->expectException(ConversionException::class);
        $this->expectExceptionMessage('Could not convert PHP value');
        $this->type->convertToPHPValue(new stdClass(), $this->platform);
    }

    /**
     * @throws ConversionException
     */
    public function testConvertToPHPValue(): void
    {
        $this->platform->expects($this->never())->method('getStringTypeDeclarationSQL');
        static::assertNull($this->type->convertToPHPValue(null, $this->platform));
        static::assertEquals(Uri::new('https://example.com/'), $this->type->convertToPHPValue('https://example.com/', $this->platform));
    }
}
