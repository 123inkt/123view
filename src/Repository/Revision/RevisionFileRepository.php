<?php

declare(strict_types=1);

namespace DR\Review\Repository\Revision;

use Doctrine\Persistence\ManagerRegistry;
use DR\Review\Doctrine\EntityRepository\ServiceEntityRepository;
use DR\Review\Entity\Revision\RevisionFile;

/**
 * @extends ServiceEntityRepository<RevisionFile>
 */
class RevisionFileRepository extends ServiceEntityRepository
{
    /**
     * @codeCoverageIgnore
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RevisionFile::class);
    }
}
