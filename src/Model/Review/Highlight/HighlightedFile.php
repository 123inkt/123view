<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Model\Review\Highlight;

class HighlightedFile
{
    /**
     * @param array<int, string> $lines
     */
    public function __construct(public readonly string $filePath, public readonly array $lines)
    {
    }

    public function getLine(int $lineNumber): ?string
    {
        return $this->lines[$lineNumber - 1] ?? null;
    }
}
