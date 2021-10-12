<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Twig\Highlight;

interface HighlighterInterface
{
    public function highlight(string $input, string $prefix, string $suffix): string;
}
