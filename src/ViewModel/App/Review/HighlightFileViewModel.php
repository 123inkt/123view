<?php
declare(strict_types=1);

namespace DR\Review\ViewModel\App\Review;

use DR\Review\Entity\Git\Diff\DiffLine;
use DR\Review\Model\Review\Highlight\HighlightedFile;

readonly class HighlightFileViewModel
{
    public function __construct(public ?HighlightedFile $file)
    {
    }

    public function getLine(int $lineNumber, DiffLine $diffLine): string
    {
        $highlightLine = $this->file?->getLine($lineNumber);
        $codeLine      = $diffLine->getLine();

        if ($highlightLine === null) {
            return $codeLine;
        }

        // remove html and html entities from the highlighted line
        $strippedLine = html_entity_decode(strip_tags($highlightLine));

        // only use the highlighted line if it is equal to the line of code
        return $strippedLine === $codeLine ? $highlightLine : htmlspecialchars($codeLine);
    }
}
