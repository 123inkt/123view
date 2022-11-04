<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;
use DR\GitCommitNotification\Entity\Review\NotificationStatus;

class NotificationStatusType extends Type
{
    public const TYPE = 'type_notification_status';

    public function getName(): string
    {
        return self::TYPE;
    }

    public function canRequireSQLConversion(): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return 'INT UNSIGNED';
    }

    /**
     * @inheritDoc
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): mixed
    {
        if ($value === null) {
            return null;
        }

        if (is_int($value) === false && (is_string($value) === false || is_numeric($value) === false)) {
            throw ConversionException::conversionFailedInvalidType($value, $this->getName(), ['null', 'NotificationStatus']);
        }

        $intValue = (int)$value;
        if ($intValue === 0) {
            return null;
        }

        return new NotificationStatus($intValue);
    }

    /**
     * @inheritDoc
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform): mixed
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof NotificationStatus) {
            return $value->getStatus() === 0 ? null : $value->getStatus();
        }

        throw ConversionException::conversionFailedInvalidType($value, $this->getName(), ['null', 'NotificationStatus']);
    }
}
