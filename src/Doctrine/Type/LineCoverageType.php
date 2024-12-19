<?php
declare(strict_types=1);

namespace DR\Review\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Exception\InvalidType;
use Doctrine\DBAL\Types\Exception\ValueNotConvertible;
use Doctrine\DBAL\Types\Type;
use DR\Review\Entity\Report\LineCoverage;
use Nette\Utils\JsonException;

class LineCoverageType extends Type
{
    public const string TYPE = "line_coverage_type";

    /**
     * @inheritDoc
     */
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getBinaryTypeDeclarationSQL($column);
    }

    /**
     * @inheritDoc
     * @throws JsonException
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof LineCoverage === false) {
            throw ValueNotConvertible::new($value, 'LineCoverage');
        }

        return $value->toBinaryString();
    }

    /**
     * @inheritDoc
     * @throws JsonException
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): ?LineCoverage
    {
        if ($value === null) {
            return null;
        }

        if (is_string($value) === false) {
            throw InvalidType::new($value, 'string', ['string']);
        }

        return LineCoverage::fromBinaryString($value);
    }

    public function getName(): string
    {
        return static::TYPE;
    }
}
