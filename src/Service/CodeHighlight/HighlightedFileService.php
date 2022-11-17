<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service\CodeHighlight;

use DR\GitCommitNotification\Entity\Review\Revision;
use DR\GitCommitNotification\Model\Review\Highlight\HighlightedFile;
use DR\GitCommitNotification\Service\Git\Show\GitShowService;
use Exception;
use Highlight\Highlighter;

class HighlightedFileService
{
    public function __construct(
        private readonly GitShowService $showService,
        private readonly ExtensionToLanguageTranslator $translator,
        private readonly Highlighter $highlighter,
        private readonly HighlightHtmlLineSplitter $splitter
    ) {
    }

    /**
     * @throws Exception
     */
    public function getHighlightedFile(Revision $revision, string $filePath): HighlightedFile
    {
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        $data      = $this->showService->getFileAtRevision($revision, $filePath);

        $languageName = $this->translator->translate($extension);
        if ($languageName === null) {
            $lines = explode("\n", htmlspecialchars($data, ENT_QUOTES));
        } else {
            $lines = $this->splitter->split($this->highlighter->highlight($languageName, $data)->value);
        }

        return new HighlightedFile($filePath, $lines);
    }
}
