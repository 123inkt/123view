<?php
declare(strict_types=1);

namespace DR\Review\Repository\Report;

use Doctrine\Persistence\ManagerRegistry;
use DR\Review\Doctrine\EntityRepository\ServiceEntityRepository;
use DR\Review\Entity\Report\CodeCoverageReport;

/**
 * @extends ServiceEntityRepository<CodeCoverageReport>
 * @method CodeCoverageReport|null find($id, $lockMode = null, $lockVersion = null)
 * @method CodeCoverageReport|null findOneBy(array $criteria, array $orderBy = null)
 * @method CodeCoverageReport[]    findAll()
 * @method CodeCoverageReport[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CodeCoverageReportRepository extends ServiceEntityRepository
{
    /**
     * @codeCoverageIgnore
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CodeCoverageReport::class);
    }
}
