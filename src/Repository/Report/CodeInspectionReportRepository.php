<?php
declare(strict_types=1);

namespace DR\Review\Repository\Report;

use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Doctrine\Persistence\ManagerRegistry;
use DR\Review\Doctrine\EntityRepository\ServiceEntityRepository;
use DR\Review\Entity\Report\CodeInspectionReport;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Revision\Revision;

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

    /**
     * For the given set of revisions find all inspection reports and the branchId if available
     *
     * @param Revision[] $revisions
     *
     * @return array<array{0: string, 1: string}>
     */
    public function findBranchIds(Repository $repository, array $revisions): array
    {
        $hashes = array_values(array_map(static fn(Revision $rev): string => $rev->getCommitHash(), $revisions));
        /** @var array<array{inspectionId: string, branchId: string}> $rows */
        $rows = $this->getEntityManager()
            ->createQueryBuilder()
            ->select(['r.inspectionId', 'r.branchId'])
            ->from($this->getEntityName(), 'r')
            ->where('r.commitHash IN (:hashes)')
            ->andWhere('r.repository = :repositoryId')
            ->andWhere('r.branchId IS NOT NULL')
            ->setParameter('hashes', $hashes)
            ->setParameter('repositoryId', $repository->getId())
            ->groupBy('r.inspectionId')
            ->getQuery()
            ->getArrayResult();

        $result = [];
        foreach ($rows as $row) {
            $result[] = [$row['inspectionId'], $row['branchId']];
        }

        return $result;
    }

    /**
     * @param Revision[]                         $revisions
     * @param array<array{0: string, 1: string}> $branchIds
     *
     * @return CodeInspectionReport[]
     */
    public function findByRevisions(Repository $repository, array $revisions, array $branchIds): array
    {
        $conn   = $this->getEntityManager()->getConnection();
        $qb     = $conn->createQueryBuilder();
        $params = [
            'hashes'       => array_values(array_map(static fn(Revision $rev): string => $rev->getCommitHash(), $revisions)),
            'repositoryId' => $repository->getId()
        ];

        $filterExpr = [$qb->expr()->in('commit_hash', ':hashes')];
        foreach ($branchIds as $index => [$inspectionId, $branchId]) {
            $filterExpr[]                    = (string)$qb->expr()->and(
                $qb->expr()->eq('inspection_id', ':inspectionId' . $index),
                $qb->expr()->eq('branch_id', ':branchId' . $index),
            );
            $params['inspectionId' . $index] = $inspectionId;
            $params['branchId' . $index]     = $branchId;
        }

        // find most recent report for each inspectionId given commit hashes and branchIds
        $subQuery = $conn->createQueryBuilder()
            ->select('inspection_id', 'MAX(create_timestamp) AS create_timestamp')
            ->from('code_inspection_report')
            ->where($qb->expr()->or(...$filterExpr))
            ->andWhere('repository_id = :repositoryId')
            ->groupBy('inspection_id');

        // inner join to get full entity data
        $query = $conn->createQueryBuilder()
            ->select('report.*')
            ->from('code_inspection_report', 'report')
            ->innerJoin(
                'report',
                sprintf('(%s)', $subQuery->getSQL()),
                'filter',
                (string)$qb->expr()->and(
                    $qb->expr()->eq('report.inspection_id', 'filter.inspection_id'),
                    $qb->expr()->eq('report.create_timestamp', 'filter.create_timestamp')
                )
            );

        // create ResultSetMapping to transform to Entity
        $rsm = new ResultSetMappingBuilder($this->getEntityManager());
        $rsm->addRootEntityFromClassMetadata(CodeInspectionReport::class, 'report');

        /** @var CodeInspectionReport[] $result */
        $result = $this->getEntityManager()->createNativeQuery($query->getSQL(), $rsm)->setParameters($params)->getResult();

        return $result;
    }

    public function cleanUp(int $beforeTimestamp): int
    {
        $qb = $this->createQueryBuilder('c');
        $qb->where('c.createTimestamp < :timestamp');
        $qb->setParameter('timestamp', $beforeTimestamp);
        $entities = $qb->getQuery()->getResult();

        foreach ($entities as $entity) {
            $this->remove($entity);
        }
        $this->getEntityManager()->flush();

        return count($entities);
    }
}
