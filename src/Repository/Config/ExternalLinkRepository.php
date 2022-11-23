<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Repository\Config;

use Doctrine\Persistence\ManagerRegistry;
use DR\GitCommitNotification\Doctrine\EntityRepository\ServiceEntityRepository;
use DR\GitCommitNotification\Entity\Config\ExternalLink;

/**
 * @extends ServiceEntityRepository<ExternalLink>
 * @method ExternalLink|null find($id, $lockMode = null, $lockVersion = null)
 * @method ExternalLink|null findOneBy(array $criteria, array $orderBy = null)
 * @method ExternalLink[]    findAll()
 * @method ExternalLink[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ExternalLinkRepository extends ServiceEntityRepository
{
    /**
     * @codeCoverageIgnore
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ExternalLink::class);
    }
}
