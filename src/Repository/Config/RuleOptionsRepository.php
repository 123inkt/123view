<?php
declare(strict_types=1);

namespace DR\Review\Repository\Config;

use Doctrine\Persistence\ManagerRegistry;
use DR\Review\Doctrine\EntityRepository\ServiceEntityRepository;
use DR\Review\Entity\Notification\RuleOptions;

/**
 * @extends ServiceEntityRepository<RuleOptions>
 * @method RuleOptions|null find($id, $lockMode = null, $lockVersion = null)
 * @method RuleOptions|null findOneBy(array $criteria, array $orderBy = null)
 * @method RuleOptions[]    findAll()
 * @method RuleOptions[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RuleOptionsRepository extends ServiceEntityRepository
{
    /**
     * @codeCoverageIgnore
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RuleOptions::class);
    }
}
