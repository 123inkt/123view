<?php
declare(strict_types=1);

namespace DR\Review\Service\Util;

class MessageSanitizer
{
    /**
     * Apply search-replace pairs to sanitize sensitive values from a log string.
     *
     * @param array<string, string> $replacements
     */
    public function sanitize(string $value, array $replacements): string
    {
        foreach ($replacements as $search => $replace) {
            $value = str_replace($search, $replace, $value);
        }

        return $value;
    }
}
