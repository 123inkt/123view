<?php
declare(strict_types=1);

namespace DR\Review\QueryParser\Term;

class EmptyMatch implements TermInterface
{
    public function __toString(): string
    {
        return '<empty>';
    }
}
