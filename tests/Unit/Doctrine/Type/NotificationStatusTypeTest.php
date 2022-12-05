<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use DR\Review\Doctrine\Type\NotificationStatusType;
use DR\Review\Entity\Review\NotificationStatus;
use DR\Review\Tests\AbstractTestCase;
use stdClass;

/**
 * @coversDefaultClass \DR\Review\Doctrine\Type\NotificationStatusType
 */
class NotificationStatusTypeTest extends AbstractTestCase
{
    private NotificationStatusType $statusType;

    public function setUp(): void
    {
        parent::setUp();
        $this->statusType = new NotificationStatusType();
    }

    /**
     * @covers ::convertToPHPValue
     * @throws ConversionException
     */
    public function testConvertToPHPValueNullValueShouldBeIgnore(): void
    {
        static::assertNull($this->statusType->convertToPHPValue(null, $this->createMock(AbstractPlatform::class)));
    }

    /**
     * @covers ::convertToPHPValue
     * @throws ConversionException
     */
    public function testConvertToPHPValueInvalidDataTypeShouldThrowException(): void
    {
        $this->expectException(ConversionException::class);
        $this->expectExceptionMessage('Could not convert PHP value \'foobar\' to type type_notification_status.');
        $this->statusType->convertToPHPValue('foobar', $this->createMock(AbstractPlatform::class));
    }

    /**
     * @covers ::convertToPHPValue
     * @throws ConversionException
     */
    public function testConvertToPHPValueIntZeroShouldBeNull(): void
    {
        static::assertNull($this->statusType->convertToPHPValue(0, $this->createMock(AbstractPlatform::class)));
        static::assertNull($this->statusType->convertToPHPValue('0', $this->createMock(AbstractPlatform::class)));
    }

    /**
     * @covers ::convertToPHPValue
     * @throws ConversionException
     */
    public function testConvertToPHPValue(): void
    {
        /** @var NotificationStatus $status */
        $status = $this->statusType->convertToPHPValue(123, $this->createMock(AbstractPlatform::class));
        static::assertSame(123, $status->getStatus());
    }

    /**
     * @covers ::getName
     */
    public function testGetName(): void
    {
        static::assertSame('type_notification_status', $this->statusType->getName());
    }

    /**
     * @covers ::getSQLDeclaration
     */
    public function testGetSQLDeclaration(): void
    {
        static::assertSame('INT UNSIGNED', $this->statusType->getSQLDeclaration([], $this->createMock(AbstractPlatform::class)));
    }

    /**
     * @covers ::canRequireSQLConversion
     */
    public function testCanRequireSQLConversion(): void
    {
        static::assertTrue($this->statusType->canRequireSQLConversion());
    }

    /**
     * @covers ::convertToDatabaseValue
     * @throws ConversionException
     */
    public function testConvertToDatabaseValueNullShouldReturnNull(): void
    {
        static::assertNull($this->statusType->convertToDatabaseValue(null, $this->createMock(AbstractPlatform::class)));
    }

    /**
     * @covers ::convertToDatabaseValue
     * @throws ConversionException
     */
    public function testConvertToDatabaseValueInvalidTypeShouldThrowException(): void
    {
        $this->expectException(ConversionException::class);
        $this->expectExceptionMessage('Could not convert PHP value of type stdClass to type type_notification_status.');
        $this->statusType->convertToDatabaseValue(new stdClass(), $this->createMock(AbstractPlatform::class));
    }

    /**
     * @covers ::convertToDatabaseValue
     * @throws ConversionException
     */
    public function testConvertToDatabaseValue(): void
    {
        static::assertNull($this->statusType->convertToDatabaseValue(new NotificationStatus(), $this->createMock(AbstractPlatform::class)));
        static::assertSame(123, $this->statusType->convertToDatabaseValue(new NotificationStatus(123), $this->createMock(AbstractPlatform::class)));
    }
}
