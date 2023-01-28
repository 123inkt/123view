<?php
declare(strict_types=1);

namespace DR\Review\Repository\Review;

use Doctrine\Persistence\ManagerRegistry;
use DR\Review\Doctrine\EntityRepository\ServiceEntityRepository;
use DR\Review\Entity\Review\CodeReviewActivity;

/**
 * @extends ServiceEntityRepository<CodeReviewActivity>
 * @method CodeReviewActivity|null find($id, $lockMode = null, $lockVersion = null)
 * @method CodeReviewActivity|null findOneBy(array $criteria, array $orderBy = null)
 * @method CodeReviewActivity[]    findAll()
 * @method CodeReviewActivity[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CodeReviewActivityRepository extends ServiceEntityRepository
{
    /**
     * @codeCoverageIgnore
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CodeReviewActivity::class);
    }

    /**
     * @param string[] $events
     *
     * @return CodeReviewActivity[]
     */
    public function findForUser(int $userId, array $events = []): array
    {
        $qb = $this->createQueryBuilder('a')
            ->select('a', 'r')
            ->innerJoin('a.review', 'r')
            ->where('(a.user != :userId OR a.user IS NULL)')
            ->andWhere('JSON_CONTAINS(r.actors, :userId) = 1')
            ->setParameter('userId', (string)$userId)
            ->orderBy('a.createTimestamp', 'DESC')
            ->setMaxResults(30);

        if (count($events) > 0) {
            $qb->andWhere('a.eventName IN (:events)')->setParameter('events', $events);
        }

        /** @var CodeReviewActivity[] $result */
        $result = $qb->getQuery()->getResult();

        return $result;
    }
}
