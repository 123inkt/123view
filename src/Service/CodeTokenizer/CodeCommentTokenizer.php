<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service\CodeTokenizer;

class CodeCommentTokenizer
{
    public function isCommentStart(StringReader $reader): bool
    {
        return $reader->current() === '/' && $reader->peek() === '/';
    }

    public function readComment(StringReader $reader): string
    {
        $result = '';

        while ($reader->eol() === false) {
            $result .= $reader->current();
            $reader->next();
        }

        return $result;
    }
}
