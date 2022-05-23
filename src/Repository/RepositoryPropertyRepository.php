<?php

namespace DR\GitCommitNotification\Repository;

use DR\GitCommitNotification\Entity\RepositoryProperty;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

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

//    /**
//     * @return RepositoryProperty[] Returns an array of RepositoryProperty objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('r.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?RepositoryProperty
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
