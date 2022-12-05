<?php
declare(strict_types=1);

namespace DR\Review\Twig;

use DR\Review\Twig\InlineCss\CssToInlineStyles;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * Intentionally using custom InlineCssExtension instead of the twig/cssinliner-extra as the internal library that's being used
 * to inline css will reformat the html. This will break any html that has the whitespace:pre styling.
 */
class InlineCssExtension extends AbstractExtension
{
    private CssToInlineStyles $inliner;

    public function __construct(CssToInlineStyles $inliner)
    {
        $this->inliner = $inliner;
    }

    /**
     * @return TwigFilter[]
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('inline_css', [$this, 'inlineCss'], ['is_safe' => ['all']]),
        ];
    }

    public function inlineCss(string $body, string ...$css): string
    {
        return $this->inliner->convert($body, implode("\n", $css));
    }
}
