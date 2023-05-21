<?php
declare(strict_types=1);

namespace DR\Review\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;
use DR\JBDiff\Util\BitSet;

class BitSetType extends Type
{
    public const TYPE = "bit_set_type";

    /**
     * @inheritDoc
     */
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getBinaryTypeDeclarationSQL($column);
    }

    /**
     * @inheritDoc
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof BitSet === false) {
            throw ConversionException::conversionFailedInvalidType($value, $this->getName(), ['null', 'BitSet']);
        }

        return $value->toBinaryString();
    }

    /**
     * @inheritDoc
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): ?BitSet
    {
        if ($value === null) {
            return null;
        }

        if (is_string($value) === false) {
            throw ConversionException::conversionFailedInvalidType($value, $this->getName(), ['null', 'string']);
        }

        return BitSet::fromBinaryString($value);
    }

    public function getName(): string
    {
        return static::TYPE;
    }
}
