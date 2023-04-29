<?php
declare(strict_types=1);

namespace DR\Review\Repository\Report;

use Doctrine\Persistence\ManagerRegistry;
use DR\Review\Doctrine\EntityRepository\ServiceEntityRepository;
use DR\Review\Entity\Report\CodeInspectionReport;

/**
 * @extends ServiceEntityRepository<CodeInspectionReport>
 * @method CodeInspectionReport|null find($id, $lockMode = null, $lockVersion = null)
 * @method CodeInspectionReport|null findOneBy(array $criteria, array $orderBy = null)
 * @method CodeInspectionReport[]    findAll()
 * @method CodeInspectionReport[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CodeInspectionReportRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CodeInspectionReport::class);
    }

    public function cleanUp(int $beforeTimestamp): int
    {
        $qb = $this->createQueryBuilder('c');
        $qb->where('c.createTimestamp < :timestamp');
        $qb->setParameter('timestamp', $beforeTimestamp);
        /** @var CodeInspectionReport[] $entities */
        $entities = $qb->getQuery()->getResult();

        foreach ($entities as $entity) {
            $this->remove($entity);
        }
        $this->getEntityManager()->flush();

        return count($entities);
    }
}
