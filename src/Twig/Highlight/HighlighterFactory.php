<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Twig\Highlight;

class HighlighterFactory
{
    public function getHighlighter(string $language): ?HighlighterInterface
    {
        switch ($language) {
            case PHPHighlighter::EXTENSION:
                return new PHPHighlighter();
            case TwigHighlighter::EXTENSION:
                return new TwigHighlighter();
            default:
                return null;
        }
    }
}
