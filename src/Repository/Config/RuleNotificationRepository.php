<?php
declare(strict_types=1);

namespace DR\Review\Repository\Config;

use Doctrine\Persistence\ManagerRegistry;
use DR\Review\Doctrine\EntityRepository\ServiceEntityRepository;
use DR\Review\Entity\Notification\RuleNotification;
use DR\Review\Entity\User\User;

/**
 * @extends ServiceEntityRepository<RuleNotification>
 * @method RuleNotification|null find($id, $lockMode = null, $lockVersion = null)
 * @method RuleNotification|null findOneBy(array $criteria, array $orderBy = null)
 * @method RuleNotification[]    findAll()
 * @method RuleNotification[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RuleNotificationRepository extends ServiceEntityRepository
{
    /**
     * @codeCoverageIgnore
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RuleNotification::class);
    }

    /**
     * @return RuleNotification[]
     */
    public function getNotificationsForUser(User $user): array
    {
        /** @var RuleNotification[] $result */
        $result = $this->createQueryBuilder('n')
            ->innerJoin('n.rule', 'r')
            ->where('r.user = :user')
            ->setParameter('user', $user)
            ->orderBy('n.createTimestamp', 'DESC')
            ->setMaxResults(100)
            ->getQuery()
            ->getResult();

        return $result;
    }
}
