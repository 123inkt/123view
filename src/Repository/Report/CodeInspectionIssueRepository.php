<?php

declare(strict_types=1);

namespace DR\Review\Repository\Report;

use Doctrine\Persistence\ManagerRegistry;
use DR\Review\Doctrine\EntityRepository\ServiceEntityRepository;
use DR\Review\Entity\Report\CodeInspectionIssue;
use DR\Review\Entity\Report\CodeInspectionReport;

/**
 * @extends ServiceEntityRepository<CodeInspectionIssue>
 * @method CodeInspectionIssue|null find($id, $lockMode = null, $lockVersion = null)
 * @method CodeInspectionIssue|null findOneBy(array $criteria, array $orderBy = null)
 * @method CodeInspectionIssue[]    findAll()
 * @method CodeInspectionIssue[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CodeInspectionIssueRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CodeInspectionIssue::class);
    }

    /**
     * @param CodeInspectionReport[] $reports
     *
     * @return CodeInspectionIssue[]
     */
    public function findByFile(array $reports, string $filePath): array
    {
        $reportIds = array_map(static fn(CodeInspectionReport $report) => (int)$report->getId(), $reports);

        $query = $this->createQueryBuilder('i')
            ->where('i.report IN (:reportIds)')
            ->setParameter('reportIds', $reportIds)
            ->andWhere('i.file = :filePath')
            ->setParameter('filePath', $filePath)
            ->getQuery();

        /** @var CodeInspectionIssue[] $results */
        $results = $query->getResult();

        return $results;
    }
}
