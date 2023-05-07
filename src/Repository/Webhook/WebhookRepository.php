<?php
declare(strict_types=1);

namespace DR\Review\Repository\Webhook;

use Doctrine\Persistence\ManagerRegistry;
use DR\Review\Doctrine\EntityRepository\ServiceEntityRepository;
use DR\Review\Entity\Webhook\Webhook;

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

    /**
     * @return Webhook[]
     */
    public function findByRepositoryId(int $repositoryId, ?bool $enabled = null): array
    {
        $qb = $this->createQueryBuilder('w')
            ->join('w.repositories', 'r')
            ->where('r.id = :repositoryId')
            ->setParameter('repositoryId', $repositoryId);

        if ($enabled !== null) {
            $qb->andWhere('w.enabled = :enabled')->setParameter('enabled', $enabled);
        }

        /** @var Webhook[] $results */
        $results = $qb->getQuery()
            ->getResult();

        return $results;
    }
}
