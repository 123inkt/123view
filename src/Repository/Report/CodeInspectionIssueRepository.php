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
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CodeInspectionIssue::class);
    }

    public function findByFile(int $repositoryId, string $commitHash, string $filePath): array
    {
        //$this->createQueryBuilder('i')
        //    ->select('i', 'r')
        //    ->innerJoin('i.report', 'r')
        //    ->where('r.repository = :repositoryId')
        //    ->setParameter('repositoryId', $repositoryId)
        //    ->andWhere('i.file = :filePath')
        //    ->setParameter('filePath', $filePath)
        //    ->andWhere('r.commitHash = :commitHash')
        //    ->setParameter();
    }
}
