<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Repository\Config;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use DR\GitCommitNotification\Entity\Config\RuleOptions;

/**
 * @extends ServiceEntityRepository<RuleOptions>
 * @method RuleOptions|null find($id, $lockMode = null, $lockVersion = null)
 * @method RuleOptions|null findOneBy(array $criteria, array $orderBy = null)
 * @method RuleOptions[]    findAll()
 * @method RuleOptions[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RuleOptionsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RuleOptions::class);
    }
}
