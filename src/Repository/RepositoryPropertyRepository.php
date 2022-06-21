<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use DR\GitCommitNotification\Entity\RepositoryProperty;

/**
 * @extends ServiceEntityRepository<RepositoryProperty>
 *
 * @method RepositoryProperty|null find($id, $lockMode = null, $lockVersion = null)
 * @method RepositoryProperty|null findOneBy(array $criteria, array $orderBy = null)
 * @method RepositoryProperty[]    findAll()
 * @method RepositoryProperty[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RepositoryPropertyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RepositoryProperty::class);
    }

    public function add(RepositoryProperty $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(RepositoryProperty $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
