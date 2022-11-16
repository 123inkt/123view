<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Twig;

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

        // breakdown single newlines in a newline for markdown aswell.
        return preg_replace("/([^>])\n/", "\$1<br>\n", $result);
    }
}
