<?php
declare(strict_types=1);

namespace DR\Review\Service\CodeHighlight;

use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Model\Review\Highlight\HighlightedFile;
use Exception;
use Highlight\Highlighter;

class HighlightedFileService
{
    /** To ensure performance, skip highlighting for files larger than */
    public const MAX_LINE_COUNT = 3000;

    public function __construct(
        private readonly FilenameToLanguageTranslator $translator,
        private readonly Highlighter $highlighter,
        private readonly HighlightHtmlLineSplitter $splitter
    ) {
    }

    /**
     * @throws Exception
     */
    public function fromDiffFile(DiffFile $diffFile): ?HighlightedFile
    {
        $languageName = $this->translator->translate($diffFile->getPathname());
        if ($languageName === null || $diffFile->getTotalNrOfLines() >= self::MAX_LINE_COUNT || count($diffFile->getBlocks()) > 1) {
            return null;
        }

        $lines = $diffFile->getLines();
        $lines = $this->splitter->split($this->highlighter->highlight($languageName, implode("\n", $lines))->value);

        return new HighlightedFile($diffFile->getPathname(), $lines);
    }
}
