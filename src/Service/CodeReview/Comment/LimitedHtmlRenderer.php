<?php
declare(strict_types=1);

namespace DR\Review\Service\CodeReview\Comment;

use DR\Utils\Assert;
use League\CommonMark\Node\Node;
use League\CommonMark\Node\StringContainerInterface;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Util\HtmlFilter;

final class LimitedHtmlRenderer implements NodeRendererInterface
{
    // allow <details>, <summary> and html comments
    private const ALLOWED_HTML_PATTERN = '/(<!--.*?-->|<\/?(?:details|summary)\s*>)/i';

    /**
     * @inheritDoc
     */
    public function render(Node $node, ChildNodeRendererInterface $childRenderer): string
    {
        $node  = Assert::isInstanceOf($node, StringContainerInterface::class);
        $parts = Assert::notFalse(preg_split(self::ALLOWED_HTML_PATTERN, $node->getLiteral(), -1, PREG_SPLIT_DELIM_CAPTURE));

        $result = '';
        foreach ($parts as $index => $part) {
            $result .= $index % 2 === 0 ? HtmlFilter::filter($part, HtmlFilter::ESCAPE) : $part;
        }

        return $result;
    }
}
