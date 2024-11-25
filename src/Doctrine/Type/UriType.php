<?php
declare(strict_types=1);

namespace DR\Review\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Exception\InvalidType;
use Doctrine\DBAL\Types\Exception\ValueNotConvertible;
use Doctrine\DBAL\Types\Type;
use League\Uri\Contracts\UriInterface;
use League\Uri\Uri;

class UriType extends Type
{
    public const string TYPE = "uri_type";

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

        if ($value instanceof UriInterface === false) {
            throw ValueNotConvertible::new($value, 'string');
        }

        return (string)$value;
    }

    /**
     * @inheritDoc
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): ?UriInterface
    {
        if ($value === null) {
            return null;
        }

        if (is_string($value) === false) {
            throw InvalidType::new($value, 'string', ['null', 'string']);
        }

        return Uri::new($value);
    }

    public function getName(): string
    {
        return static::TYPE;
    }
}
