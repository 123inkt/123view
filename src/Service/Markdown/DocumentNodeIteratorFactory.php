<?php
declare(strict_types=1);

namespace DR\Review\Service\Markdown;

use Generator;
use League\CommonMark\Node\Node;

class DocumentNodeIteratorFactory
{
    /**
     * @return Generator<Node>
     */
    public function iterate(Node $node): Generator
    {
        yield $node;

        for ($next = $node->firstChild(); $next !== null; $next = $next->next()) {
            yield from $this->iterate($next);
        }
    }
}
