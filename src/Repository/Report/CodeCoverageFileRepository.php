<?php
declare(strict_types=1);

namespace DR\Review\Repository\Report;

use Doctrine\Persistence\ManagerRegistry;
use DR\Review\Doctrine\EntityRepository\ServiceEntityRepository;
use DR\Review\Entity\Report\CodeCoverageFile;

/**
 * @extends ServiceEntityRepository<CodeCoverageFile>
 * @method CodeCoverageFile|null find($id, $lockMode = null, $lockVersion = null)
 * @method CodeCoverageFile|null findOneBy(array $criteria, array $orderBy = null)
 * @method CodeCoverageFile[]    findAll()
 * @method CodeCoverageFile[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CodeCoverageFileRepository extends ServiceEntityRepository
{
    /**
     * @codeCoverageIgnore
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CodeCoverageFile::class);
    }
}
