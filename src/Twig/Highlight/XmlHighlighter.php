<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Twig\Highlight;

class XmlHighlighter implements HighlighterInterface
{
    public const EXTENSION = 'xml';

    public function highlight(string $input, string $prefix, string $suffix): string
    {
        return (string)preg_replace('/([\w-]+)=/', $prefix . '$1' . $suffix . '=', $input);
    }
}
