<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Repository\Webhook;

use Doctrine\Persistence\ManagerRegistry;
use DR\GitCommitNotification\Doctrine\EntityRepository\ServiceEntityRepository;
use DR\GitCommitNotification\Entity\Webhook\Webhook;

/**
 * @extends ServiceEntityRepository<Webhook>
 * @method Webhook|null find($id, $lockMode = null, $lockVersion = null)
 * @method Webhook|null findOneBy(array $criteria, array $orderBy = null)
 * @method Webhook[]    findAll()
 * @method Webhook[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WebhookRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Webhook::class);
    }
}
