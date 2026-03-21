<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Repository\Expression;

use Doctrine\ORM\Query\Expr\Andx;
use Doctrine\ORM\Query\Expr\Comparison;
use Doctrine\ORM\Query\Expr\Func;
use Doctrine\ORM\Query\Expr\Orx;
use DR\Review\QueryParser\Term\Operator\AndOperator;
use DR\Review\QueryParser\Term\Operator\NotOperator;
use DR\Review\QueryParser\Term\Operator\OrOperator;
use DR\Review\QueryParser\Term\TermInterface;
use DR\Review\Repository\Expression\QueryExpressionFactory;
use DR\Review\Tests\AbstractTestCase;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(QueryExpressionFactory::class)]
class QueryExpressionFactoryTest extends AbstractTestCase
{
    public function testCreateFromFailOnUnknownTerm(): void
    {
        $term    = static::createStub(TermInterface::class);
        $factory = new QueryExpressionFactory([static fn() => null]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported term: ');
        $factory->createFrom($term);
    }

    public function testCreateFromSingleExpression(): void
    {
        $expression = static::createStub(Comparison::class);
        $term       = static::createStub(TermInterface::class);

        $factory = new QueryExpressionFactory([static fn($val) => $val === $term ? $expression : null]);
        [$actualExpression,] = $factory->createFrom($term);

        static::assertSame($expression, $actualExpression);
    }

    public function testCreateFromNotOperator(): void
    {
        $expression = static::createStub(Comparison::class);
        $term       = static::createStub(TermInterface::class);
        $operator   = new NotOperator($term);

        $factory = new QueryExpressionFactory([static fn() => $expression]);
        [$actualExpression,] = $factory->createFrom($operator);

        static::assertInstanceOf(Func::class, $actualExpression);
        static::assertSame('NOT', $actualExpression->getName());
    }

    public function testCreateFromAndOperator(): void
    {
        $expression = new Comparison('left', '=', 'right');
        $termLeft   = static::createStub(TermInterface::class);
        $termRight  = static::createStub(TermInterface::class);
        $operator   = new AndOperator($termLeft, $termRight);

        $factory = new QueryExpressionFactory([static fn() => $expression]);
        [$actualExpression,] = $factory->createFrom($operator);

        static::assertInstanceOf(Andx::class, $actualExpression);
    }

    public function testCreateFromOrOperator(): void
    {
        $expression = new Comparison('left', '=', 'right');
        $termLeft   = static::createStub(TermInterface::class);
        $termRight  = static::createStub(TermInterface::class);
        $operator   = new OrOperator($termLeft, $termRight);

        $factory = new QueryExpressionFactory([static fn() => $expression]);
        [$actualExpression,] = $factory->createFrom($operator);

        static::assertInstanceOf(Orx::class, $actualExpression);
    }
}
