<?php
declare(strict_types=1);

namespace DR\Review\Repository\Report;

use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Doctrine\Persistence\ManagerRegistry;
use DR\Review\Doctrine\EntityRepository\ServiceEntityRepository;
use DR\Review\Entity\Report\CodeInspectionReport;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Utility\Assert;

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
     * @return array<string, string>
     */
    public function findBranchIds(Repository $repository, array $revisions): array
    {
        $hashes = array_values(array_map(static fn(Revision $rev): string => (string)$rev->getCommitHash(), $revisions));
        $rows   = Assert::isArray(
            $this->getEntityManager()
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
                ->getArrayResult()
        );

        $result = [];
        foreach ($rows as $row) {
            $result[(string)$row['inspectionId']] = (string)$row['branchId'];
        }

        return $result;
    }

    /**
     * @param Revision[]            $revisions
     * @param array<string, string> $branchIds
     *
     * @return CodeInspectionReport[]
     */
    public function findByRevisions(Repository $repository, array $revisions, array $branchIds = []): array
    {
        $params = [
            'hashes'       => array_values(array_map(static fn(Revision $rev): string => (string)$rev->getCommitHash(), $revisions)),
            'repositoryId' => $repository->getId()
        ];

        $branchSql = [];
        $index     = 1;
        foreach ($branchIds as $inspectionId => $branchId) {
            $branchSql[]                     = sprintf('OR (inspection_id = :inspectionId%d AND branch_id = :branchId%d)', $index, $index);
            $params['inspectionId' . $index] = $inspectionId;
            $params['branchId' . $index]     = $branchId;
            ++$index;
        }

        $rsm = new ResultSetMappingBuilder($this->getEntityManager());
        $rsm->addRootEntityFromClassMetadata(CodeInspectionReport::class, 'report');

        /** @var CodeInspectionReport[] $result */
        $result = $this->getEntityManager()
            ->createNativeQuery(
                'SELECT report.*
                 FROM   code_inspection_report report
                 INNER JOIN (
                    SELECT   inspection_id, MAX(create_timestamp) AS create_timestamp
                    FROM     code_inspection_report
                    WHERE    (commit_hash IN (:hashes) ' . implode(' ', $branchSql) . ')
                    AND      repository_id = :repositoryId
                    GROUP BY inspection_id
                 ) AS `filter`
                 ON  report.inspection_id = filter.inspection_id
                 AND report.create_timestamp = filter.create_timestamp',
                $rsm
            )
            ->setParameters($params)
            ->getResult();

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
