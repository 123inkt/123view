<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

class SpaceSeparatedStringValueType extends Type
{
    public const TYPE = "space_separated_string_type";

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
    public function convertToDatabaseValue($value, AbstractPlatform $platform): string
    {
        return implode(" ", $value);
    }

    /**
     * @inheritDoc
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        return array_filter(explode(" ", $value), static fn($val) => $val !== '');
    }

    public function getName(): string
    {
        return static::TYPE;
    }

    /**
     * @inheritDoc
     */
    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return false;
    }
}
