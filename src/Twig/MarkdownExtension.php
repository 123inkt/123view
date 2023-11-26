<?php
declare(strict_types=1);

namespace DR\Review\Twig;

use DR\Review\Service\Markdown\MarkdownConverterService;
use League\CommonMark\Exception\CommonMarkException;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class MarkdownExtension extends AbstractExtension
{
    public function __construct(private readonly MarkdownConverterService $converter)
    {
    }

    /**
     * @return TwigFilter[]
     */
    public function getFilters(): array
    {
        return [new TwigFilter('markdown', [$this, 'convert'], ['is_safe' => ['all']])];
    }

    /**
     * @throws CommonMarkException
     */
    public function convert(string $string): string
    {
        return $this->converter->convert($string);
    }
}
