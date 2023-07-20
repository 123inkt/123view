<?php
declare(strict_types=1);

namespace DR\Review\QueryParser\Term\Operator;

use DR\Review\QueryParser\Term\TermInterface;
use DR\Utils\Arrays;
use InvalidArgumentException;

class AndOperator implements TermInterface
{
    public function __construct(public readonly TermInterface $leftTerm, public readonly TermInterface $rightTerm)
    {
    }

    public static function create(TermInterface ...$terms): self
    {
        if (count($terms) <= 1) {
            throw new InvalidArgumentException('At least two terms are required to create an AND operator');
        }

        $leftTerm = array_shift($terms);
        if (count($terms) === 1) {
            return new AndOperator($leftTerm, Arrays::first($terms));
        }

        return new AndOperator($leftTerm, AndOperator::create(...$terms));
    }

    public function __toString(): string
    {
        return '(' . $this->leftTerm . ') AND (' . $this->rightTerm . ')';
    }
}
