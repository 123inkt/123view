<?php

declare(strict_types=1);

namespace DR\Review\Repository\Mcp;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\Persistence\ManagerRegistry;
use DR\Review\Doctrine\EntityRepository\ServiceEntityRepository;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Model\Mcp\CodeReviewQuery;

/**
 * @extends ServiceEntityRepository<CodeReview>
 */
class CodeReviewRepository extends ServiceEntityRepository
{
    private readonly AbstractPlatform $platform;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CodeReview::class);
        $this->platform = $this->getEntityManager()->getConnection()->getDatabasePlatform();
    }

    /**
     * Find code reviews matching all provided filters. All filters are optional; when multiple are given they are
     * combined with AND. Results are ordered by most recently updated.
     *
     * @return CodeReview[]
     */
    public function findByFilters(CodeReviewQuery $query, int $limit): array
    {
        $qb = $this->createQueryBuilder('c')
            ->innerJoin('c.repository', 'r')
            ->addSelect('r')
            ->orderBy('c.updateTimestamp', 'DESC')
            ->setMaxResults($limit);

        if ($query->title !== null) {
            $qb->andWhere("c.title LIKE :title ESCAPE '!'")
                ->setParameter('title', '%' . $this->platform->escapeStringForLike($query->title, '!') . '%');
        }

        if ($query->repositoryUrl !== null) {
            $qb->andWhere('r.url = :repositoryUrl')
                ->setParameter('repositoryUrl', $query->repositoryUrl);
        }

        if ($query->branchName !== null || $query->authorEmail !== null) {
            $qb->innerJoin('c.revisions', 'rv')->distinct();

            if ($query->branchName !== null) {
                $qb->andWhere('rv.firstBranch = :branchName')
                    ->setParameter('branchName', $query->branchName);
            }

            if ($query->authorEmail !== null) {
                $qb->andWhere('rv.authorEmail = :authorEmail')
                    ->setParameter('authorEmail', $query->authorEmail);
            }
        }

        /** @var CodeReview[] $reviews */
        $reviews = $qb->getQuery()->getResult();

        return $reviews;
    }
}
