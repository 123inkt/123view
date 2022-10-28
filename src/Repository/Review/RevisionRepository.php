<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Repository\Review;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use DR\GitCommitNotification\Entity\Config\Repository;
use DR\GitCommitNotification\Entity\Config\User;
use DR\GitCommitNotification\Entity\Review\CodeReview;
use DR\GitCommitNotification\Entity\Review\Revision;

/**
 * @extends ServiceEntityRepository<Revision>
 * @method Revision|null find($id, $lockMode = null, $lockVersion = null)
 * @method Revision|null findOneBy(array $criteria, array $orderBy = null)
 * @method Revision[]    findAll()
 * @method Revision[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RevisionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Revision::class);
    }

    public function exists(Repository $repository, Revision $revision): bool
    {
        return $this->findOneBy(['repository' => $repository->getId(), 'commitHash' => $revision->getCommitHash()]) !== null;
    }

    public function save(Revision $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Revision $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function flush(): void
    {
        $this->getEntityManager()->flush();
    }

    /**
     * @return Paginator<CodeReview>
     */
    public function getPaginatorForSearchQuery(int $repositoryId, int $page, string $searchQuery): Paginator
    {
        $query = $this->createQueryBuilder('r')
            ->leftJoin('r.review', 'c')
            ->where('r.repository = :repositoryId')
            ->setParameter('repositoryId', $repositoryId)
            ->orderBy('r.createTimestamp', 'DESC')
            ->setFirstResult(max(0, $page - 1) * 50)
            ->setMaxResults(50);

        if ($searchQuery !== '') {
            $query->andWhere('r.title LIKE :searchQuery OR r.authorEmail LIKE :searchQuery OR r.authorName LIKE: searchQuery');
            $query->setParameter('searchQuery', '%' . addcslashes($searchQuery, '%_') . '%');
        }

        return new Paginator($query->getQuery(), false);
    }
}
