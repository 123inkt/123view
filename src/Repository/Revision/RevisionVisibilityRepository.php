<?php
declare(strict_types=1);

namespace DR\Review\Repository\Revision;

use Doctrine\Persistence\ManagerRegistry;
use DR\Review\Doctrine\EntityRepository\ServiceEntityRepository;
use DR\Review\Entity\Revision\RevisionVisibility;

/**
 * @extends ServiceEntityRepository<RevisionVisibility>
 * @method RevisionVisibility|null find($id, $lockMode = null, $lockVersion = null)
 * @method RevisionVisibility|null findOneBy(array $criteria, array $orderBy = null)
 * @method RevisionVisibility[]    findAll()
 * @method RevisionVisibility[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RevisionVisibilityRepository extends ServiceEntityRepository
{
    /**
     * @codeCoverageIgnore
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RevisionVisibility::class);
    }
}
