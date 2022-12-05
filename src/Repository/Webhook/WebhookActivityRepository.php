<?php
declare(strict_types=1);

namespace DR\Review\Repository\Webhook;

use Doctrine\Persistence\ManagerRegistry;
use DR\Review\Doctrine\EntityRepository\ServiceEntityRepository;
use DR\Review\Entity\Webhook\WebhookActivity;

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
