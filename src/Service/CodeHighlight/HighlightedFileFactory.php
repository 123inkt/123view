<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service\CodeHighlight;

use Highlight\Highlighter;

class HighlightedFileFactory
{
    public function __construct(private readonly Highlighter $highlighter) { }

    public function createFromString(string $languageName, string $data)
    {
        $html = $this->highlighter->highlight($languageName, $data);
    }
}
