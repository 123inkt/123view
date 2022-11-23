<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Helper;

use Doctrine\ORM\Query;
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

    public function where(string $string): self
    {
        $this->queryBuilder->expects(atLeastOnce())->method('where')->with($string)->willReturnSelf();

        return $this;
    }

    public function andWhere(string $string): self
    {
        $this->queryBuilder->expects(atLeastOnce())->method('andWhere')->with($string)->willReturnSelf();

        return $this;
    }

    public function setParameter(string $key, mixed $value): self
    {
        $this->queryBuilder->expects(atLeastOnce())->method('setParameter')->with($key, $value)->willReturnSelf();

        return $this;
    }

    public function getResult(array $values): self
    {
        /** @var Query&MockObject $query */
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
