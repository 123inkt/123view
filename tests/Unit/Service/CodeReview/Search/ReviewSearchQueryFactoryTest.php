<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\CodeReview\Search;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use DR\Review\QueryParser\ParserFactory;
use DR\Review\QueryParser\Term\Match\MatchWord;
use DR\Review\Service\CodeReview\Search\ReviewSearchQueryExpressionFactory;
use DR\Review\Service\CodeReview\Search\ReviewSearchQueryFactory;
use DR\Review\Service\CodeReview\Search\ReviewSearchQueryParserFactory;
use DR\Review\Tests\AbstractTestCase;
use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(ReviewSearchQueryFactory::class)]
class ReviewSearchQueryFactoryTest extends AbstractTestCase
{
    private ReviewSearchQueryParserFactory&MockObject     $parserFactory;
    private ReviewSearchQueryExpressionFactory&MockObject $expressionFactory;
    private ReviewSearchQueryFactory                      $queryFactory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parserFactory     = $this->createMock(ReviewSearchQueryParserFactory::class);
        $this->expressionFactory = $this->createMock(ReviewSearchQueryExpressionFactory::class);
        $this->queryFactory      = new ReviewSearchQueryFactory($this->parserFactory, $this->expressionFactory);
    }

    /**
     * @throws Exception
     */
    public function testAddSearchQuery(): void
    {
        $expression = $this->createMock(Expr\Composite::class);
        $params     = new ArrayCollection(['foo' => 'bar']);

        $parser = ParserFactory::tokens(ParserFactory::stringLiteral()->map(static fn($val) => new MatchWord($val)));
        $this->parserFactory->expects(self::once())->method('createParser')->willReturn($parser);

        $this->expressionFactory->expects(self::once())->method('createFrom')->willReturn([$expression, $params]);

        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder->expects(self::once())->method('andWhere')->with($expression);
        $queryBuilder->expects(self::once())->method('setParameter')->with('foo', 'bar');

        $this->queryFactory->addSearchQuery($queryBuilder, 'searchQuery');
    }
}
