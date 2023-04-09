<?php
declare(strict_types=1);

namespace DR\Review\Repository\Expression;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Query\Expr;
use DR\Review\QueryParser\Term\Operator\AndOperator;
use DR\Review\QueryParser\Term\Operator\NotOperator;
use DR\Review\QueryParser\Term\Operator\OrOperator;
use DR\Review\QueryParser\Term\TermInterface;
use InvalidArgumentException;

/**
 * @phpstan-type Expression = Expr\Composite|Expr\Func|Expr\Comparison
 * @phpstan-type Params = ArrayCollection<int|string, mixed>
 */
class QueryExpressionFactory
{
    /**
     * @param array<callable(TermInterface, Params): ?Expression> $callables
     */
    public function __construct(private readonly array $callables)
    {
    }

    /**
     * @return array{Expression, Params}
     */
    public function createFrom(TermInterface $expression): array
    {
        /** @phpstan-var Params $params */
        $params = new ArrayCollection();

        $expression = $this->createWithParam($expression, $params);

        return [$expression, $params];
    }

    /**
     * @param Params $params
     */
    private function createWithParam(TermInterface $expression, Collection $params): Expr\Composite|Expr\Func|Expr\Comparison
    {
        if ($expression instanceof AndOperator) {
            return new Expr\Andx([$this->createWithParam($expression->leftTerm, $params), $this->createWithParam($expression->rightTerm, $params)]);
        }
        if ($expression instanceof OrOperator) {
            return new Expr\Orx([$this->createWithParam($expression->leftTerm, $params), $this->createWithParam($expression->rightTerm, $params)]);
        }
        if ($expression instanceof NotOperator) {
            return new Expr\Func('NOT', $this->createWithParam($expression->term, $params));
        }

        foreach ($this->callables as $callable) {
            $result = $callable($expression, $params);
            if ($result !== null) {
                return $result;
            }
        }

        throw new InvalidArgumentException('Unsupported term: ' . get_class($expression));
    }
}
