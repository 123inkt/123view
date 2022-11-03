<?php

namespace DR\GitCommitNotification\Repository\Review;

use DR\GitCommitNotification\Entity\Review\FileSeenStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FileSeenStatus>
 *
 * @method FileSeenStatus|null find($id, $lockMode = null, $lockVersion = null)
 * @method FileSeenStatus|null findOneBy(array $criteria, array $orderBy = null)
 * @method FileSeenStatus[]    findAll()
 * @method FileSeenStatus[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FileSeenStatusRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FileSeenStatus::class);
    }

    public function save(FileSeenStatus $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(FileSeenStatus $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return FileSeenStatus[] Returns an array of FileSeenStatus objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('f.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?FileSeenStatus
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
