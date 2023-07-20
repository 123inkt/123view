<?php
declare(strict_types=1);

namespace DR\Review\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use DR\Utils\Assert;

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
        return $value === null ? "" : implode(" ", Assert::isArray($value));
    }

    /**
     * @inheritDoc
     * @return string[]|null
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): ?array
    {
        return $value === null ? null : array_filter(explode(" ", Assert::string($value)), static fn($val) => $val !== '');
    }

    public function getName(): string
    {
        return static::TYPE;
    }
}
