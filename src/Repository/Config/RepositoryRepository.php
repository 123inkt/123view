<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Repository\Config;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use DR\GitCommitNotification\Entity\Config\Repository;

/**
 * @extends ServiceEntityRepository<Repository>
 * @method Repository|null find($id, $lockMode = null, $lockVersion = null)
 * @method Repository|null findOneBy(array $criteria, array $orderBy = null)
 * @method Repository[]    findAll()
 * @method Repository[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RepositoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Repository::class);
    }

    public function save(Repository $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Repository $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return Repository[]
     */
    public function findByUpdateRevisions(): array
    {
        $qb = $this->createQueryBuilder('r');
        $qb
            ->where('r.active = 1')
            ->andWhere(
                $qb->expr()->orX(
                    'r.updateRevisionsTimestamp + r.updateRevisionsInterval < :currentTime',
                    'r.updateRevisionsTimestamp IS NULL'
                )
            )
            ->setParameter('currentTime', time());

        return $qb->getQuery()->getResult();
    }
}
