<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Helper;

use Doctrine\Common\Collections\Expr\Comparison;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr\Composite;
use Doctrine\ORM\Query\Expr\OrderBy;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\MockObject\MockBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use function PHPUnit\Framework\atLeastOnce;

class QueryBuilderAssertion
{
    public function __construct(private readonly TestCase $testCase, private readonly QueryBuilder&MockObject $queryBuilder)
    {
    }

    public function select(string $alias): self
    {
        $this->queryBuilder->expects(atLeastOnce())->method('select')->with($alias)->willReturnSelf();

        return $this;
    }

    public function from(string $from, string $alias, ?string $indexBy = null): self
    {
        $this->queryBuilder->expects(atLeastOnce())->method('from')->with($from, $alias, $indexBy)->willReturnSelf();

        return $this;
    }

    public function leftJoin(
        string $join,
        string $alias,
        ?string $conditionType = null,
        string|Comparison|Composite|null $condition = null,
        ?string $indexBy = null
    ): self {
        $this->queryBuilder->expects(atLeastOnce())->method('leftJoin')->with($join, $alias, $conditionType, $condition, $indexBy)->willReturnSelf();

        return $this;
    }

    public function where(mixed $string): self
    {
        $this->queryBuilder->expects(atLeastOnce())->method('where')->with($string)->willReturnSelf();

        return $this;
    }

    public function andWhere(mixed $string): self
    {
        $this->queryBuilder->expects(atLeastOnce())->method('andWhere')->with($string)->willReturnSelf();

        return $this;
    }

    public function andWhereConsecutive(mixed ...$args): self
    {
        $this->queryBuilder->expects(atLeastOnce())->method('andWhere')->withConsecutive(...$args)->willReturnSelf();

        return $this;
    }

    public function setParameter(string $key, mixed $value): self
    {
        $this->queryBuilder->expects(atLeastOnce())->method('setParameter')->with($key, $value)->willReturnSelf();

        return $this;
    }

    public function setParameterConsecutive(mixed ...$args): self
    {
        $this->queryBuilder->expects(atLeastOnce())->method('setParameter')->withConsecutive(...$args)->willReturnSelf();

        return $this;
    }

    public function orderBy(string|OrderBy $sort, ?string $order = null): self
    {
        $this->queryBuilder->expects(atLeastOnce())->method('orderBy')->with($sort, $order)->willReturnSelf();

        return $this;
    }

    public function setFirstResult(?int $firstResult): self
    {
        $this->queryBuilder->expects(atLeastOnce())->method('setFirstResult')->with($firstResult)->willReturnSelf();

        return $this;
    }

    public function setMaxResults(?int $maxResults): self
    {
        $this->queryBuilder->expects(atLeastOnce())->method('setMaxResults')->with($maxResults)->willReturnSelf();

        return $this;
    }

    public function getResult(mixed $values): self
    {
        $query = (new MockBuilder($this->testCase, Query::class))
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->disallowMockingUnknownTypes()
            ->getMock();

        $query->expects(atLeastOnce())->method('getResult')->willReturn($values);
        $this->queryBuilder->expects(atLeastOnce())->method('getQuery')->willReturn($query);

        return $this;
    }
}
