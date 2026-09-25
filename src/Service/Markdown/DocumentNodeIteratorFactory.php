<?php
declare(strict_types=1);

namespace DR\Review\Service\Markdown;

use League\CommonMark\Node\Node;

class DocumentNodeIteratorFactory
{
    /**
     * @return iterable<Node>
     */
    public function iterate(Node $node): iterable
    {
        yield $node;

        for ($next = $node->firstChild(); $next !== null; $next = $next->next()) {
            yield from $this->iterate($next);
        }
    }
}
