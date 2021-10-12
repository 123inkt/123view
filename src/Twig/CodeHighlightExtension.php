<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Twig;

use DR\GitCommitNotification\Twig\Highlight\HighlighterFactory;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class CodeHighlightExtension extends AbstractExtension
{
    private HighlighterFactory $highlighterFactory;

    public function __construct(HighlighterFactory $highlighterFactory)
    {
        $this->highlighterFactory = $highlighterFactory;
    }

    /**
     * @return TwigFilter[]
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('highlight', [$this, 'highlight'], ['is_safe' => ['html']]),
        ];
    }

    public function highlight(?string $input, string $language, string $className, string $htmlTag = 'span'): string
    {
        $language    = strtolower($language);
        $highlighter = $this->highlighterFactory->getHighlighter($language);

        if ($input === null || $highlighter === null) {
            return htmlspecialchars((string)$input, ENT_QUOTES);
        }

        $prefix = sprintf('<%s class="%s">', $htmlTag, $className);
        $suffix = sprintf('</%s>', $htmlTag);

        return $highlighter->highlight(htmlspecialchars($input, ENT_QUOTES), $prefix, $suffix);
    }
}
