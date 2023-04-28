<?php
declare(strict_types=1);

namespace DR\Review\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;
use DR\JBDiff\Util\BitSet;
use DR\Review\Utility\Assert;

class BitSetType extends Type
{
    public const TYPE = "bit_set_type";

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

        if ($value instanceof BitSet === false) {
            throw ConversionException::conversionFailedInvalidType($value, $this->getName(), ['null', 'BitSet']);
        }

        return serialize($value);
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

        // test
        return Assert::instanceOf(BitSet::class, unserialize($value, ['allowed_classes' => [BitSet::class]]));
    }

    public function getName(): string
    {
        return static::TYPE;
    }
}
