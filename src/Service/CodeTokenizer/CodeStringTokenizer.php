<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service\CodeTokenizer;

use InvalidArgumentException;

class CodeStringTokenizer
{
    private const ENCAPSULATE_CHAR = ['"', "'"];
    private const ESCAPE_CHAR      = '\\';

    public function readString(StringReader $reader): string
    {
        $currentChar  = $startChar = (string)$reader->current();
        $previousChar = null;
        $result       = $currentChar;

        if (in_array($startChar, self::ENCAPSULATE_CHAR, true) === false) {
            throw new InvalidArgumentException('Expecting string to start with either " or \'.');
        }

        while ($reader->eol() === false) {
            $currentChar = $reader->next();
            $result      .= $currentChar;

            if ($previousChar === null && $currentChar === self::ESCAPE_CHAR) {
                $previousChar = $currentChar;
                continue;
            }

            // escape char is escaped, skip
            if ($currentChar === self::ESCAPE_CHAR && $previousChar === self::ESCAPE_CHAR) {
                $previousChar = null;
                continue;
            }

            if ($currentChar === $startChar && $previousChar !== self::ESCAPE_CHAR) {
                break;
            }
        }

        return $result;
    }
}
