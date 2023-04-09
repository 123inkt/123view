<?php
declare(strict_types=1);

namespace DR\Review\Service\CodeReview\Search;

use Doctrine\ORM\QueryBuilder;
use DR\Review\QueryParser\Term\TermInterface;
use Exception;
use Parsica\Parsica\ParserHasFailed;

class ReviewSearchQueryFactory
{
    public function __construct(
        private readonly ReviewSearchQueryParserFactory $parserFactory,
        private readonly ReviewSearchQueryExpressionFactory $expressionFactory
    ) {
    }

    /**
     * @throws ParserHasFailed|Exception
     */
    public function addSearchQuery(QueryBuilder $queryBuilder, string $searchQuery): void
    {
        /** @var TermInterface $terms */
        $terms = $this->parserFactory->createParser()->tryString($searchQuery)->output();

        [$expression, $parameters] = $this->expressionFactory->createFrom($terms);

        $queryBuilder->andWhere($expression);
        $queryBuilder->setParameters($parameters);
    }
}
