<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Twig\Highlight;

use DR\GitCommitNotification\Service\CodeTokenizer\CodeTokenizer;

class HighlighterFactory
{
    private readonly HighlightPattern $highlightPattern;

    public function __construct(private readonly CodeTokenizer $tokenizer)
    {
        $this->highlightPattern = new HighlightPattern();
    }

    public function getHighlighter(string $language): ?HighlighterInterface
    {
        return match ($language) {
            PHPHighlighter::EXTENSION        => new PHPHighlighter($this->highlightPattern, $this->tokenizer),
            TwigHighlighter::EXTENSION       => new TwigHighlighter(),
            TypescriptHighlighter::EXTENSION => new TypescriptHighlighter(),
            XmlHighlighter::EXTENSION        => new XmlHighlighter(),
            default                          => null,
        };
    }
}
