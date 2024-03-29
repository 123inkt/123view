<?php
declare(strict_types=1);

namespace DR\Review\QueryParser\Term\Operator;

use DR\Review\QueryParser\Term\TermInterface;

class NotOperator implements TermInterface
{
    public function __construct(public readonly TermInterface $term)
    {
    }

    public function __toString(): string
    {
        return 'NOT (' . $this->term . ')';
    }
}
