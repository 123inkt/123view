<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service\CodeTokenizer;

class CodeTokenizer
{
    public const TOKEN_CODE    = 1;
    public const TOKEN_STRING  = 2;
    public const TOKEN_COMMENT = 3;

    public function __construct(private readonly CodeStringTokenizer $stringTokenizer, private readonly CodeCommentTokenizer $commentTokenizer)
    {
    }

    /**
     * @return array<array{0: int, 1: string}>
     */
    public function tokenize(string $string): array
    {
        $reader       = new StringReader($string);
        $tokens       = [];
        $currentToken = '';

        do {
            $char = $reader->current();

            if (in_array($char, CodeStringTokenizer::ENCAPSULATE_CHAR, true)) {
                if ($currentToken !== '') {
                    $tokens[]     = [self::TOKEN_CODE, $currentToken];
                    $currentToken = '';
                }
                $tokens[] = [self::TOKEN_STRING, $this->stringTokenizer->readString($reader)];
            } elseif ($this->commentTokenizer->isCommentStart($reader)) {
                if ($currentToken !== '') {
                    $tokens[]     = [self::TOKEN_CODE, $currentToken];
                    $currentToken = '';
                }
                $tokens[] = [self::TOKEN_COMMENT, $this->commentTokenizer->readComment($reader)];
            } else {
                $currentToken .= $char;
            }
        } while ($reader->next() !== null);

        if ($currentToken !== '') {
            $tokens[] = [self::TOKEN_CODE, $currentToken];
        }

        return $tokens;
    }
}
