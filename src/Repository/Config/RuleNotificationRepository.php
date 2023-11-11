<?php
declare(strict_types=1);

namespace DR\Review\Repository\Config;

use Doctrine\DBAL\Exception;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use DR\Review\Doctrine\EntityRepository\ServiceEntityRepository;
use DR\Review\Entity\Notification\RuleNotification;
use DR\Review\Entity\User\User;
use DR\Utils\Assert;

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
     * @throws ORMException
     */
    public function getUnreadNotificationCountForUser(User $user): int
    {
        $query = $this->createQueryBuilder('n')
            ->select('count(n.id)')
            ->innerJoin('n.rule', 'r')
            ->where('r.user = :user')
            ->setParameter('user', $user)
            ->andWhere('n.read = 0')
            ->getQuery();

        return Assert::integer($query->getSingleScalarResult());
    }

    /**
     * @return array<int, int>
     * @throws Exception
     */
    public function getUnreadNotificationPerRuleCount(User $user): array
    {
        $conn  = $this->getEntityManager()->getConnection();
        $query = $conn->createQueryBuilder()
            ->select('r.id', 'COUNT(1) AS `count`')
            ->from('rule_notification', 'n')
            ->innerJoin('n', 'rule', 'r', 'r.id = n.rule_id')
            ->innerJoin('r', 'user', 'u', 'u.id = r.user_id')
            ->where('u.id = :userId')
            ->setParameter('userId', $user->getId())
            ->andWhere('r.active = 1')
            ->andWhere('n.read = 0')
            ->groupBy('r.id');

        /** @var array<int, int> $result */
        $result = $conn->executeQuery($query->getSQL(), $query->getParameters())->fetchAllKeyValue();

        return $result;
    }
}
