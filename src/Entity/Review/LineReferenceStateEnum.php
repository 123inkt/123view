<?php

declare(strict_types=1);

namespace DR\Review\Entity\Review;

enum LineReferenceStateEnum: string
{
    case Modified = 'M';
    case Unmodified = 'U';
    case Added = 'A';
    case Deleted = 'D';
    case Unknown = '?';

    /**
     * @return string[]
     */
    public static function values(): array
    {
        return array_map(static fn(LineReferenceStateEnum $value): string => $value->value, self::cases());
    }
}
