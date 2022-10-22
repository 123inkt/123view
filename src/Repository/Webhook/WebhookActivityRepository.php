<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Repository\Webhook;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
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
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WebhookActivity::class);
    }

    public function save(WebhookActivity $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(WebhookActivity $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
