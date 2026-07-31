<?php
declare(strict_types=1);

namespace DR\Review\Repository\Ai;

use Doctrine\Persistence\ManagerRegistry;
use DR\Review\Doctrine\EntityRepository\ServiceEntityRepository;
use DR\Review\Entity\Ai\AiReviewReference;

/**
 * @extends ServiceEntityRepository<AiReviewReference>
 * @method AiReviewReference|null find($id, $lockMode = null, $lockVersion = null)
 * @method AiReviewReference|null findOneBy(array<string, mixed> $criteria, array<string, string>|null $orderBy = null)
 * @method AiReviewReference[]    findAll()
 * @method AiReviewReference[]    findBy(array<string, mixed> $criteria, array<string, string>|null $orderBy = null, $limit = null, $offset = null)
 */
class AiReviewReferenceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AiReviewReference::class);
    }

    /**
     * @return AiReviewReference[]
     */
    public function findAllEnabled(): array
    {
        /** @var AiReviewReference[] $results */
        $results = $this->createQueryBuilder('r')
            ->where('r.enabled = :enabled')
            ->setParameter('enabled', true)
            ->orderBy('r.priority', 'DESC')
            ->addOrderBy('r.id', 'ASC')
            ->getQuery()
            ->getResult();

        return $results;
    }
}
