<?php

declare(strict_types=1);

namespace DR\Review\Repository\Report;

use Doctrine\Persistence\ManagerRegistry;
use DR\Review\Doctrine\EntityRepository\ServiceEntityRepository;
use DR\Review\Entity\Report\CodeInspectionIssue;

/**
 * @extends ServiceEntityRepository<CodeInspectionIssue>
 * @method CodeInspectionIssue|null find($id, $lockMode = null, $lockVersion = null)
 * @method CodeInspectionIssue|null findOneBy(array $criteria, array $orderBy = null)
 * @method CodeInspectionIssue[]    findAll()
 * @method CodeInspectionIssue[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CodeInspectionIssueRepository extends ServiceEntityRepository
{
    /**
     * @codeCoverageIgnore
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CodeInspectionIssue::class);
    }
}
