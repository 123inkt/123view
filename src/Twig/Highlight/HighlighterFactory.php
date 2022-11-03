<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Twig\Highlight;

class HighlighterFactory
{
    public function getHighlighter(string $language): ?HighlighterInterface
    {
        return match ($language) {
            PHPHighlighter::EXTENSION        => new PHPHighlighter(),
            TwigHighlighter::EXTENSION       => new TwigHighlighter(),
            TypescriptHighlighter::EXTENSION => new TypescriptHighlighter(),
            XmlHighlighter::EXTENSION        => new XmlHighlighter(),
            default                          => null,
        };
    }
}
