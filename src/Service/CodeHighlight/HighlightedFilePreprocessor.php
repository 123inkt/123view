<?php
declare(strict_types=1);

namespace DR\Review\Service\CodeHighlight;

use DR\Utils\Assert;

class HighlightedFilePreprocessor
{
    public function process(string $language, string $content): string
    {
        if ($language === "typescript") {
            // currently 2025-08-21 highlightjs does not support typescript generics completely.
            $content = Assert::string(preg_replace('#<\w+\[]>#', '', $content));
        }
        return $content;
    }
}
