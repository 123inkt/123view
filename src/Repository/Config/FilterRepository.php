<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Repository\Config;

use Doctrine\Persistence\ManagerRegistry;
use DR\GitCommitNotification\Doctrine\EntityRepository\ServiceEntityRepository;
use DR\GitCommitNotification\Entity\Notification\Filter;

/**
 * @extends ServiceEntityRepository<Filter>
 * @method Filter|null find($id, $lockMode = null, $lockVersion = null)
 * @method Filter|null findOneBy(array $criteria, array $orderBy = null)
 * @method Filter[]    findAll()
 * @method Filter[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FilterRepository extends ServiceEntityRepository
{
    /**
     * @codeCoverageIgnore
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Filter::class);
    }
}
