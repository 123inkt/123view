<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use DR\GitCommitNotification\Doctrine\Type\NotificationStatusType;
use DR\GitCommitNotification\Entity\Review\NotificationStatus;
use DR\GitCommitNotification\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Doctrine\Type\NotificationStatusType
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
     */
    public function testConvertToDatabaseValue(): void
    {
    }
}
