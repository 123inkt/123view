<?php
declare(strict_types=1);

namespace DR\Review\Service\Markdown;

use League\CommonMark\Exception\CommonMarkException;
use League\CommonMark\MarkdownConverter;

class MarkdownConverterService
{
    public function __construct(private readonly MarkdownConverter $converter)
    {
    }

    /**
     * @throws CommonMarkException
     */
    public function convert(string $string): string
    {
        $result = $this->converter->convert($string)->getContent();

        // strip <br> after <ul> and <li>
        return (string)preg_replace('/(<ul>|<\\/li>)\s*<br>/i', '$1', $result);
    }
}
