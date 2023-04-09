<?php
declare(strict_types=1);

namespace DR\Review\Repository\Review;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Service\CodeReview\Search\ReviewSearchQueryFactory;
use Parsica\Parsica\ParserHasFailed;

class CodeReviewQueryBuilder
{
    public const ORDER_CREATE_TIMESTAMP = 'create-timestamp';
    public const ORDER_UPDATE_TIMESTAMP = 'update-timestamp';

    private readonly QueryBuilder $queryBuilder;

    public function __construct(string $alias, EntityManagerInterface $em, private readonly ReviewSearchQueryFactory $searchQueryFactory)
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

    /**
     * @throws ParserHasFailed
     */
    public function search(string $searchQuery): self
    {
        if (trim($searchQuery) === "") {
            return $this;
        }

        $this->searchQueryFactory->addSearchQuery($this->queryBuilder, $searchQuery);

        return $this;
    }

    public function getQuery(): Query
    {
        return $this->queryBuilder->getQuery();
    }
}
