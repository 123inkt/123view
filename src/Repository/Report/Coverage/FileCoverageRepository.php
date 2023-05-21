<?php

namespace DR\Review\Repository\Report\Coverage;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use DR\Review\Entity\Report\CodeCoverageFile;

/**
 * @extends ServiceEntityRepository<CodeCoverageFile>
 * @method CodeCoverageFile|null find($id, $lockMode = null, $lockVersion = null)
 * @method CodeCoverageFile|null findOneBy(array $criteria, array $orderBy = null)
 * @method CodeCoverageFile[]    findAll()
 * @method CodeCoverageFile[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FileCoverageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CodeCoverageFile::class);
    }

    public function save(CodeCoverageFile $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(CodeCoverageFile $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return CodeCoverageFile[] Returns an array of CodeCoverageFile objects
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

//    public function findOneBySomeField($value): ?CodeCoverageFile
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
