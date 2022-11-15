<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Twig\Highlight;

/**
 * @codeCoverageIgnore
 */
class HighlightPattern
{
    public const COMMENT = '<span class="diff-file__code-comment">%s</span>';
    public const STRING  = '<span class="diff-file__code-string">%s</span>';
    public const KEYWORD = '<span class="diff-file__code-keyword">%s</span>';

    public function __construct(
        public readonly string $comment = self::COMMENT,
        public readonly string $string = self::STRING,
        public readonly string $keyword = self::KEYWORD
    ) {
    }
}
