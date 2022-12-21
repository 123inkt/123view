<?php
declare(strict_types=1);

namespace DR\Review\Twig;

use League\CommonMark\MarkdownConverter;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class MarkdownExtension extends AbstractExtension
{
    public function __construct(private readonly MarkdownConverter $converter)
    {
    }

    /**
     * @return TwigFilter[]
     */
    public function getFilters(): array
    {
        return [new TwigFilter('markdown', [$this, 'convert'], ['is_safe' => ['all']])];
    }

    public function convert(string $string): string
    {
        return $this->converter->convert($string)->getContent();
    }
}
