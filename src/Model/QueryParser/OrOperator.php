<?php
declare(strict_types=1);

namespace DR\Review\Model\QueryParser;

class OrOperator implements TermInterface
{
    public function __construct(public readonly TermInterface $leftTerm, public readonly TermInterface $rightTerm)
    {
    }
}
