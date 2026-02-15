<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use DR\Review\Doctrine\Type\NotificationStatusType;
use DR\Review\Entity\Review\NotificationStatus;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use stdClass;

#[CoversClass(NotificationStatusType::class)]
class NotificationStatusTypeTest extends AbstractTestCase
{
    private NotificationStatusType $statusType;

    public function setUp(): void
    {
        parent::setUp();
        $this->statusType = new NotificationStatusType();
    }

    /**
     * @throws ConversionException
     */
    public function testConvertToPHPValueNullValueShouldBeIgnore(): void
    {
        static::assertNull($this->statusType->convertToPHPValue(null, static::createStub(AbstractPlatform::class)));
    }

    /**
     * @throws ConversionException
     */
    public function testConvertToPHPValueInvalidDataTypeShouldThrowException(): void
    {
        $this->expectException(ConversionException::class);
        $this->expectExceptionMessage('Could not convert database value');
        $this->statusType->convertToPHPValue('foobar', static::createStub(AbstractPlatform::class));
    }

    /**
     * @throws ConversionException
     */
    public function testConvertToPHPValueIntZeroShouldBeNull(): void
    {
        static::assertNull($this->statusType->convertToPHPValue(0, static::createStub(AbstractPlatform::class)));
        static::assertNull($this->statusType->convertToPHPValue('0', static::createStub(AbstractPlatform::class)));
    }

    /**
     * @throws ConversionException
     */
    public function testConvertToPHPValue(): void
    {
        /** @var NotificationStatus $status */
        $status = $this->statusType->convertToPHPValue(123, static::createStub(AbstractPlatform::class));
        static::assertSame(123, $status->getStatus());
    }

    public function testGetName(): void
    {
        static::assertSame('type_notification_status', $this->statusType->getName());
    }

    public function testGetSQLDeclaration(): void
    {
        static::assertSame('INT UNSIGNED', $this->statusType->getSQLDeclaration([], static::createStub(AbstractPlatform::class)));
    }

    public function testCanRequireSQLConversion(): void
    {
        static::assertTrue($this->statusType->canRequireSQLConversion());
    }

    /**
     * @throws ConversionException
     */
    public function testConvertToDatabaseValueNullShouldReturnNull(): void
    {
        static::assertNull($this->statusType->convertToDatabaseValue(null, static::createStub(AbstractPlatform::class)));
    }

    /**
     * @throws ConversionException
     */
    public function testConvertToDatabaseValueInvalidTypeShouldThrowException(): void
    {
        $this->expectException(ConversionException::class);
        $this->expectExceptionMessage('Could not convert PHP value of type');
        $this->statusType->convertToDatabaseValue(new stdClass(), static::createStub(AbstractPlatform::class));
    }

    /**
     * @throws ConversionException
     */
    public function testConvertToDatabaseValue(): void
    {
        static::assertNull($this->statusType->convertToDatabaseValue(new NotificationStatus(), static::createStub(AbstractPlatform::class)));
        static::assertSame(123, $this->statusType->convertToDatabaseValue(new NotificationStatus(123), static::createStub(AbstractPlatform::class)));
    }
}
