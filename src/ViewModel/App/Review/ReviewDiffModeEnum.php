<?php
declare(strict_types=1);

namespace DR\Review\ViewModel\App\Review;

enum ReviewDiffModeEnum: string
{
    case SPLIT = 'split';
    case UNIFIED = 'unified';
    case INLINE = 'inline';

    /**
     * @return string[]
     */
    public static function values(): array
    {
        $values = [];
        foreach (self::cases() as $case) {
            $values[] = $case->value;
        }

        return $values;
    }
}
