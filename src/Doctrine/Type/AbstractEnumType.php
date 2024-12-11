<?php
declare(strict_types=1);

namespace DR\Review\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use InvalidArgumentException;

abstract class AbstractEnumType extends Type
{
    public const string TYPE = '';
    /** @var string[] */
    protected const array VALUES = [];

    /**
     * @inheritDoc
     */
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        $values = array_map(
            static fn(string $val): string => "'" . $val . "'",
            static::VALUES
        );

        return "ENUM(" . implode(", ", $values) . ")";
    }

    /**
     * @inheritDoc
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_string($value) === false || in_array($value, static::VALUES, true) === false) {
            throw new InvalidArgumentException("Invalid value '" . get_debug_type($value) . "' for type '" . static::TYPE . "'.");
        }

        return $value;
    }

    public function getName(): string
    {
        return static::TYPE;
    }
}
