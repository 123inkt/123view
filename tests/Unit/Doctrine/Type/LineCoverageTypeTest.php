<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use DR\Review\Doctrine\Type\LineCoverageType;
use DR\Review\Entity\Report\LineCoverage;
use DR\Review\Tests\AbstractTestCase;
use Nette\Utils\JsonException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(LineCoverageType::class)]
class LineCoverageTypeTest extends AbstractTestCase
{
    private AbstractPlatform&MockObject $platform;
    private LineCoverageType            $type;

    public function setUp(): void
    {
        parent::setUp();
        $this->platform = $this->createMock(AbstractPlatform::class);
        $this->type     = new LineCoverageType();
    }

    public function testGetSQLDeclaration(): void
    {
        $column = ['length' => 100];

        $this->platform->expects($this->once())->method('getBinaryTypeDeclarationSQL')->with($column)->willReturn('varbinary');

        static::assertSame('varbinary', $this->type->getSQLDeclaration($column, $this->platform));
    }

    public function testGetName(): void
    {
        static::assertSame(LineCoverageType::TYPE, $this->type->getName());
    }

    /**
     * @throws ConversionException|JsonException
     */
    public function testConvertToDatabaseValue(): void
    {
        $coverage = new LineCoverage();

        static::assertNull($this->type->convertToDatabaseValue(null, $this->platform));
        static::assertSame('', $this->type->convertToDatabaseValue($coverage, $this->platform));
    }

    /**
     * @throws ConversionException|JsonException
     */
    public function testConvertToDatabaseValueInvalidValue(): void
    {
        $this->expectException(ConversionException::class);
        $this->expectExceptionMessage('Could not convert database value');
        $this->type->convertToDatabaseValue('foobar', $this->platform);
    }

    /**
     * @throws ConversionException|JsonException
     */
    public function testConvertToPHPValue(): void
    {
        static::assertNull($this->type->convertToPHPValue(null, $this->platform));
        static::assertInstanceOf(LineCoverage::class, $this->type->convertToPHPValue('', $this->platform));
    }

    /**
     * @throws ConversionException|JsonException
     */
    public function testConvertToPHPValueInvalidValue(): void
    {
        $this->expectException(ConversionException::class);
        $this->expectExceptionMessage('Could not convert PHP value');
        $this->type->convertToPHPValue(12345, $this->platform);
    }
}
