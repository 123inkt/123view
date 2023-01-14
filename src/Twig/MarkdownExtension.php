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
        $result = $this->converter->convert($string)->getContent();

        // strip <br> after <ul> and <li>
        return (string)preg_replace('/(<ul>|<\\/li>)\s*<br>/i', '$1', $result);
    }
}
