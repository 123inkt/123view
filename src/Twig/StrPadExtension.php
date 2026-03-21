<?php
declare(strict_types=1);

namespace DR\Review\Twig;

use InvalidArgumentException;
use Twig\Attribute\AsTwigFilter;

class StrPadExtension
{
    /**
     * Twig wrapper for str_pad to Pad a string to a fixed length.
     * example usage in template:
     * {{ page.title|strpad(50, 'left') }}
     */
    #[AsTwigFilter(name: 'strpad', isSafe: ['html'])]
    public function strpad(int|string|null $input, int $padLength, string $padType = 'left'): string
    {
        $strPadType = match ($padType) {
            'left'  => STR_PAD_LEFT,
            'right' => STR_PAD_RIGHT,
            'both'  => STR_PAD_BOTH,
            default => throw new InvalidArgumentException(sprintf('%s isn\'t a valid pad type', $padType)),
        };

        $value = str_pad((string)$input, $padLength, ' ', $strPadType);

        // escape properly
        $value = htmlspecialchars($value, ENT_QUOTES);

        // replace space with nbsp
        return str_replace(' ', '&nbsp;', $value);
    }
}
