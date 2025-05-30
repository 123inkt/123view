<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use DR\Review\Doctrine\Type\SpaceSeparatedStringValueType;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(SpaceSeparatedStringValueType::class)]
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

    public function testGetSQLDeclaration(): void
    {
        $column = ['length' => 100];

        $this->platform->expects($this->once())->method('getStringTypeDeclarationSQL')->with($column)->willReturn('varchar');

        static::assertSame('varchar', $this->type->getSQLDeclaration($column, $this->platform));
    }

    public function testGetName(): void
    {
        static::assertSame(SpaceSeparatedStringValueType::TYPE, $this->type->getName());
    }

    /**
     * @throws ConversionException
     */
    public function testConvertToDatabaseValue(): void
    {
        static::assertSame('foo bar', $this->type->convertToDatabaseValue(['foo', 'bar'], $this->platform));
    }

    /**
     * @throws ConversionException
     */
    public function testConvertToPHPValue(): void
    {
        $result = $this->type->convertToPHPValue(' foo bar', $this->platform);
        static::assertNotNull($result);
        static::assertSame(['foo', 'bar'], array_values($result));
    }
}
