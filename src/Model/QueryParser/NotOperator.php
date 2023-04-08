<?php
declare(strict_types=1);

namespace DR\Review\Model\QueryParser;

class NotOperator implements TermInterface
{
    public function __construct(public readonly TermInterface $term)
    {
    }
}
