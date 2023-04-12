<?php
declare(strict_types=1);

namespace DR\Review\Repository\Review;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\QueryParser\Term\EmptyMatch;
use DR\Review\QueryParser\Term\TermInterface;
use DR\Review\Service\CodeReview\Search\ReviewSearchQueryExpressionFactory;

class CodeReviewQueryBuilder
{
    public const ORDER_CREATE_TIMESTAMP = 'create-timestamp';
    public const ORDER_UPDATE_TIMESTAMP = 'update-timestamp';

    private readonly QueryBuilder $queryBuilder;

    public function __construct(string $alias, EntityManagerInterface $em, private readonly ReviewSearchQueryExpressionFactory $expressionFactory)
    {
        $this->queryBuilder = $em->createQueryBuilder()->select($alias)->from(CodeReview::class, $alias);
    }

    public function prepare(?int $repositoryId): self
    {
        $this->queryBuilder
            ->select('r', 'rv', 'rvwr', 'u')
            ->leftJoin('r.revisions', 'rv')
            ->leftJoin('r.reviewers', 'rvwr')
            ->leftJoin('rvwr.user', 'u');

        if ($repositoryId !== null) {
            $this->queryBuilder
                ->where('r.repository = :repositoryId')
                ->setParameter('repositoryId', $repositoryId);
        }

        return $this;
    }

    public function orderBy(string $orderBy): self
    {
        if ($orderBy === self::ORDER_UPDATE_TIMESTAMP) {
            $this->queryBuilder->orderBy('r.updateTimestamp', 'DESC');
        } else {
            $this->queryBuilder->orderBy('r.id', 'DESC');
        }

        return $this;
    }

    public function paginate(int $page, int $pageSize): self
    {
        $this->queryBuilder
            ->setFirstResult(max(0, $page - 1) * $pageSize)
            ->setMaxResults($pageSize);

        return $this;
    }

    public function search(TermInterface $searchQuery): self
    {
        if ($searchQuery instanceof EmptyMatch) {
            return $this;
        }

        [$expression, $parameters] = $this->expressionFactory->createFrom($searchQuery);

        $this->queryBuilder->andWhere($expression);
        foreach ($parameters as $name => $value) {
            $this->queryBuilder->setParameter($name, $value);
        }

        return $this;
    }

    public function getQuery(): Query
    {
        return $this->queryBuilder->getQuery();
    }
}
