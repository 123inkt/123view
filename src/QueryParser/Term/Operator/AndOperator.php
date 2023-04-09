<?php
declare(strict_types=1);

namespace DR\Review\QueryParser\Term\Operator;

use DR\Review\QueryParser\Term\TermInterface;

class AndOperator implements TermInterface
{
    public function __construct(public readonly TermInterface $leftTerm, public readonly TermInterface $rightTerm)
    {
    }

    public function __toString(): string
    {
        return '(' . $this->leftTerm . ') AND (' . $this->rightTerm . ')';
    }
}
