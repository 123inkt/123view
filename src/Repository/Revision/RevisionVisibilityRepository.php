<?php

namespace DR\Review\Repository\Revision;

use DR\Review\Entity\Revision\RevisionVisibility;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<RevisionVisibility>
 *
 * @method RevisionVisibility|null find($id, $lockMode = null, $lockVersion = null)
 * @method RevisionVisibility|null findOneBy(array $criteria, array $orderBy = null)
 * @method RevisionVisibility[]    findAll()
 * @method RevisionVisibility[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RevisionVisibilityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RevisionVisibility::class);
    }

    public function save(RevisionVisibility $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(RevisionVisibility $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return RevisionVisibility[] Returns an array of RevisionVisibility objects
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

//    public function findOneBySomeField($value): ?RevisionVisibility
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
