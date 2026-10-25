<?php

declare(strict_types=1);

namespace DR\Review\Entity\Review;

enum CommentStateEnum: string
{
    case Open = 'open';
    case Resolved = 'resolved';

    /**
     * @return string[]
     */
    public static function values(): array
    {
        return array_map(static fn(CommentStateEnum $value): string => $value->value, self::cases());
    }
}
