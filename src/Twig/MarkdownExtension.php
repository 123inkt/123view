<?php
declare(strict_types=1);

namespace DR\Review\Twig;

use DR\Review\Service\Markdown\MarkdownConverterService;
use League\CommonMark\Exception\CommonMarkException;
use Twig\Attribute\AsTwigFilter;

class MarkdownExtension
{
    public function __construct(private readonly MarkdownConverterService $converter)
    {
    }

    /**
     * @throws CommonMarkException
     */
    #[AsTwigFilter(name: 'markdown', isSafe: ['all'])]
    public function convert(string $string): string
    {
        return $this->converter->convert($string);
    }
}
