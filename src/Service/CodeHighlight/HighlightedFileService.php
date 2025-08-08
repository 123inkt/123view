<?php
declare(strict_types=1);

namespace DR\Review\Service\CodeHighlight;

use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Model\Review\Highlight\HighlightedFile;
use Exception;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class HighlightedFileService implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /** To ensure performance, skip highlighting for files larger than */
    public const MAX_LINE_COUNT = 3000;

    public function __construct(
        private readonly FilenameToLanguageTranslator $translator,
        private readonly HighlightService $highlightService,
        private readonly HighlightHtmlLineSplitter $splitter,
        private readonly HighlightedFilePreprocessor $preprocessor
    ) {
    }

    /**
     * @throws Exception|TransportExceptionInterface
     */
    public function fromDiffFile(DiffFile $diffFile): ?HighlightedFile
    {
        $languageName = $this->translator->translate($diffFile->getPathname());
        if ($languageName === null || $diffFile->getTotalNrOfLines() >= self::MAX_LINE_COUNT || count($diffFile->getBlocks()) > 1) {
            return null;
        }

        $content = implode("\n", $diffFile->getLines());

        // preprocess certain contents that breaks the highlightjs formatter
        $content = $this->preprocessor->process($languageName, $content);

        // highlight the content
        $result = $this->highlightService->highlight($languageName, $content);

        return $result === null ? null : new HighlightedFile($diffFile->getPathname(), fn() => $this->splitter->split($result));
    }
}
