<?php
declare(strict_types=1);

namespace DR\Review\Service\CodeReview\Comment;

use League\CommonMark\Node\Node;
use League\CommonMark\Node\RawMarkupContainerInterface;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Util\HtmlFilter;

final class LimitedHtmlRenderer implements NodeRendererInterface
{
    private const ALLOWED_HTML_PATTERN = '/(<!--[\s\S]*?-->|<\/?(?:detail|summary)\s*>)/i';

    public function render(Node $node, ChildNodeRendererInterface $childRenderer): string
    {
        unset($childRenderer);
        assert($node instanceof RawMarkupContainerInterface);

        $parts = preg_split(self::ALLOWED_HTML_PATTERN, $node->getLiteral(), -1, PREG_SPLIT_DELIM_CAPTURE);
        assert($parts !== false);

        $result = '';
        foreach ($parts as $index => $part) {
            $result .= $index % 2 === 0 ? HtmlFilter::filter($part, HtmlFilter::ESCAPE) : $part;
        }

        return $result;
    }
}
