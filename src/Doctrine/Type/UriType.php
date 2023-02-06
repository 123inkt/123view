<?php
declare(strict_types=1);

namespace DR\Review\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;
use League\Uri\Uri;

class UriType extends Type
{
    public const TYPE = "uri_type";

    /**
     * @inheritDoc
     */
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getStringTypeDeclarationSQL($column);
    }

    /**
     * @inheritDoc
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof Uri === false) {
            throw ConversionException::conversionFailedInvalidType($value, $this->getName(), ['null', 'Uri']);
        }

        return (string)$value;
    }

    /**
     * @inheritDoc
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): ?Uri
    {
        if ($value === null) {
            return null;
        }

        if (is_string($value) === false) {
            throw ConversionException::conversionFailedInvalidType($value, $this->getName(), ['null', 'string']);
        }

        return Uri::createFromString($value);
    }

    public function getName(): string
    {
        return static::TYPE;
    }
}
