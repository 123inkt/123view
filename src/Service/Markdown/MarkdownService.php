<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service\Markdown;

use League\CommonMark\MarkdownConverter;

class MarkdownService
{
    public function __construct(private readonly MarkdownConverter $converter)
    {
    }

    public function convert(string $string): string
    {
        $result = $this->converter->convert($string)->getContent();

        // breakdown single newlines in a newline for markdown aswell.
        return (string)preg_replace("/([^>])\n/", "\$1<br>\n", $result);
    }
}
