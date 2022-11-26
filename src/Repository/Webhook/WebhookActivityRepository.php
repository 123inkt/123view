<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Repository\Webhook;

use Doctrine\Persistence\ManagerRegistry;
use DR\GitCommitNotification\Doctrine\EntityRepository\ServiceEntityRepository;
use DR\GitCommitNotification\Entity\Webhook\WebhookActivity;

/**
 * @extends ServiceEntityRepository<WebhookActivity>
 * @method WebhookActivity|null find($id, $lockMode = null, $lockVersion = null)
 * @method WebhookActivity|null findOneBy(array $criteria, array $orderBy = null)
 * @method WebhookActivity[]    findAll()
 * @method WebhookActivity[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WebhookActivityRepository extends ServiceEntityRepository
{
    /**
     * @codeCoverageIgnore
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WebhookActivity::class);
    }
}
