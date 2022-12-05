<?php
declare(strict_types=1);

namespace DR\Review\Repository\Config;

use Doctrine\Persistence\ManagerRegistry;
use DR\Review\Doctrine\EntityRepository\ServiceEntityRepository;
use DR\Review\Entity\Config\ExternalLink;

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
