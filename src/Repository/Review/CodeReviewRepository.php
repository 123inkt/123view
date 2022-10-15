<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Repository\Review;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use DR\GitCommitNotification\Entity\Review\CodeReview;

/**
 * @extends ServiceEntityRepository<CodeReview>
 * @method CodeReview|null find($id, $lockMode = null, $lockVersion = null)
 * @method CodeReview|null findOneBy(array $criteria, array $orderBy = null)
 * @method CodeReview[]    findAll()
 * @method CodeReview[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CodeReviewRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CodeReview::class);
    }

    public function save(CodeReview $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(CodeReview $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return Paginator<CodeReview>
     */
    public function getPaginatorForSearchQuery(int $repositoryId, int $page, string $searchQuery): Paginator
    {
        $query = $this->createQueryBuilder('r')
            ->where('r.repository = :repositoryId')
            ->setParameter('repositoryId', $repositoryId)
            ->orderBy('r.id', 'DESC')
            ->setFirstResult(max(0, $page - 1) * 50)
            ->setMaxResults(50);

        if ($searchQuery !== '') {
            if (preg_match('/id:(\d+)/', $searchQuery, $matches) === 1) {
                $query->andWhere('r.id = :id')->setParameter('id', $matches[1]);
                $searchQuery = trim(str_replace($matches[0], '', $searchQuery));
            }

            if (preg_match('/state:(\w+)/', $searchQuery, $matches) === 1) {
                $query->andWhere('r.state = :state')->setParameter('state', $matches[1]);
                $searchQuery = trim(str_replace($matches[0], '', $searchQuery));
            }

            if ($searchQuery !== '') {
                $query->andWhere('r.title LIKE :title')->setParameter('title', '%' . addcslashes($searchQuery, "%_") . '%');
            }
        }

        return new Paginator($query->getQuery(), false);
    }

    /**
     * @throws NonUniqueResultException
     */
    public function findOneByTitle(int $repositoryId, string $reviewTitleIdentifier): ?CodeReview
    {
        /** @var CodeReview|null $review */
        $review = $this->createQueryBuilder('c')
            ->where('c.title LIKE :match')
            ->andWhere('c.repository = :repositoryId')
            ->orderBy('c.id', 'DESC')
            ->setParameter('match', $reviewTitleIdentifier . '%')
            ->setParameter('repositoryId', $repositoryId)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult(AbstractQuery::HYDRATE_OBJECT);

        return $review;
    }
}
