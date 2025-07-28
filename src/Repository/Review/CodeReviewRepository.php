<?php
declare(strict_types=1);

namespace DR\Review\Repository\Review;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use DR\Review\Doctrine\EntityRepository\ServiceEntityRepository;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\QueryParser\Term\TermInterface;
use DR\Review\Service\CodeReview\Search\ReviewSearchQueryExpressionFactory;

/**
 * @extends ServiceEntityRepository<CodeReview>
 * @method CodeReview|null find($id, $lockMode = null, $lockVersion = null)
 * @method CodeReview|null findOneBy(array $criteria, array $orderBy = null)
 * @method CodeReview[]    findAll()
 * @method CodeReview[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CodeReviewRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, private readonly ReviewSearchQueryExpressionFactory $expressionFactory)
    {
        parent::__construct($registry, CodeReview::class);
    }

    /**
     * @throws NonUniqueResultException
     */
    public function findByUrl(string $repositoryName, int $reviewProjectId): ?CodeReview
    {
        $query = $this->createQueryBuilder('c')
            ->innerJoin('c.repository', 'r', Join::WITH, 'r.name = :repositoryName')
            ->where('c.projectId = :reviewProjectId')
            ->setParameter('repositoryName', $repositoryName)
            ->setParameter('reviewProjectId', $reviewProjectId)
            ->setMaxResults(1)
            ->getQuery();

        /** @var CodeReview|null $review */
        $review = $query->getOneOrNullResult(AbstractQuery::HYDRATE_OBJECT);

        return $review;
    }

    /**
     * @throws NonUniqueResultException
     */
    public function getCreateProjectId(int $repositoryId): int
    {
        $query = $this->createQueryBuilder('c')
            ->where('c.repository = :repositoryId')
            ->setParameter('repositoryId', $repositoryId)
            ->orderBy('c.projectId', 'DESC')
            ->setMaxResults(1)
            ->getQuery();

        /** @var CodeReview|null $review */
        $review = $query->getOneOrNullResult(AbstractQuery::HYDRATE_OBJECT);

        return (int)$review?->getProjectId() + 1;
    }

    /**
     * @return Paginator<CodeReview>
     */
    public function getPaginatorForSearchQuery(
        ?int $repositoryId,
        int $page,
        TermInterface $searchQuery,
        string $searchOrderBy = CodeReviewQueryBuilder::ORDER_UPDATE_TIMESTAMP
    ): Paginator {
        $queryBuilder = (new CodeReviewQueryBuilder('r', $this->getEntityManager(), $this->expressionFactory))
            ->prepare($repositoryId)
            ->paginate($page, 50)
            ->orderBy($searchOrderBy)
            ->search($searchQuery);

        /** @var Paginator<CodeReview> $paginator */
        $paginator = new Paginator($queryBuilder->getQuery(), true);

        return $paginator;
    }

    /**
     * @throws NonUniqueResultException
     */
    public function findOneByReferenceId(int $repositoryId, string $referenceId, string $reviewType): ?CodeReview
    {
        /** @var CodeReview|null $review */
        $review = $this->createQueryBuilder('c')
            ->where('c.referenceId = :referenceId')
            ->andWhere('c.repository = :repositoryId')
            ->andWhere('c.type = :type')
            ->orderBy('c.id', 'DESC')
            ->setParameter('referenceId', $referenceId)
            ->setParameter('repositoryId', $repositoryId)
            ->setParameter('type', $reviewType)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult(AbstractQuery::HYDRATE_OBJECT);

        return $review;
    }

    /**
     * @throws NonUniqueResultException
     */
    public function findOneByCommitHash(int $repositoryId, string $commitHash): ?CodeReview
    {
        /** @var CodeReview|null $review */
        $review = $this->createQueryBuilder('c')
            ->innerJoin('c.revisions', 'r', 'WITH', 'r.commitHash = :commitHash')
            ->where('c.repository = :repositoryId')
            ->setParameter('commitHash', $commitHash)
            ->setParameter('repositoryId', $repositoryId)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult(AbstractQuery::HYDRATE_OBJECT);

        return $review;
    }

    /**
     * @return CodeReview[]
     */
    public function findByBranchName(int $repositoryId, string $branchName): array
    {
        /** @var CodeReview[] $reviews */
        $reviews = $this->createQueryBuilder('c')
            ->innerJoin('c.revisions', 'r', 'WITH', 'r.firstBranch = :branchName')
            ->where('c.repository = :repositoryId')
            ->setParameter('branchName', $branchName)
            ->setParameter('repositoryId', $repositoryId)
            ->getQuery()
            ->getResult();

        return $reviews;
    }

    /**
     * Find all CodeReviews with the same title as the given CodeReview.
     * The Repository entity is eagerly fetched to minimize database queries.
     * 
     * @return CodeReview[]
     */
    public function findByTitle(CodeReview $codeReview): array
    {
        /** @var CodeReview[] $reviews */
        $reviews = $this->createQueryBuilder('c')
            ->innerJoin('c.repository', 'r')
            ->addSelect('r')
            ->where('c.title = :title')
            ->setParameter('title', $codeReview->getTitle())
            ->orderBy('r.name', 'ASC')
            ->getQuery()
            ->getResult();

        return $reviews;
    }
}
