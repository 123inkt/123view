<?php
declare(strict_types=1);

namespace DR\Review\Entity\Review;

enum CommentVisibilityEnum: string
{
    case ALL = 'all';
    case UNRESOLVED = 'unresolved';
    case NONE = 'none';

    /**
     * @return string[]
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
