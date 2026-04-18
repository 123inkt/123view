<?php
declare(strict_types=1);

namespace DR\Review\Service\CodeOwner;

use DR\Review\Model\CodeOwner\OwnerPattern;
use DR\Utils\Assert;

readonly class CodeOwnerPatternMatcher
{
    private const string DELIMITER    = '#';
    private const array  REPLACEMENTS = [
        '*'    => '[^\/]*',
        '?'    => '[^\/]',
        '**'   => '.*',
        '/**'  => '\/.*',
        '**/'  => '(.*\/)?',
        '/**/' => '\/([^\/]+\/)*',
    ];

    public function match(string $filename, OwnerPattern $pattern): bool
    {
        // This method converts the pattern to a regular expression using the following guidelines:
        //
        // *            => [^/]+
        // ?            => [^/]
        // [<range>]    => [<range>]
        // **           => .*
        // /<pattern>   => ^<pattern>
        // <pattern>/   => <pattern>/.+$
        // <pattern>/*  => <pattern>/[^/]+$

        // construct regular expression
        $parts = Assert::isArray(preg_split('/(\/?\*\*\/?)|(\*)|\?|(\[.+?])/i', $pattern->pattern, flags: PREG_SPLIT_DELIM_CAPTURE));
        $regex = implode(array_map(
            static fn(string $part): string => self::REPLACEMENTS[$part] ?? (
                str_starts_with($part, '[') ? $part : preg_quote($part, self::DELIMITER)
            ),
            $parts
        ));

        // check whether the regex starts with `/`
        if (str_starts_with($regex, '/')) {
            $regex = '^' . substr($regex, 1);
        } else {
            $regex = '^(.+\/)?' . $regex;
        }

        // check whether the pattern ends with a `/`
        if (str_ends_with($regex, '/')) {
            $regex = substr($regex, 0, -1) . '\/.+$';
            // or whether the pattern ends with `*`, but not with `**`
        } elseif (preg_match('/[^*]+\*$/s', $pattern->pattern) === 1) {
            $regex = $regex . '$';
        } else {
            $regex = $regex . '(/.+)?$';
        }

        return preg_match(self::DELIMITER . $regex . self::DELIMITER . 's', $filename) === 1;
    }
}
