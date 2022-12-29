<?php
declare(strict_types=1);

namespace DR\Review\Repository\User;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;
use DR\Review\Doctrine\EntityRepository\ServiceEntityRepository;
use DR\Review\Entity\User\User;
use DR\Review\Utility\Assert;

/**
 * @extends ServiceEntityRepository<User>
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * @throws NoResultException|NonUniqueResultException
     */
    public function getNewUserCount(): int
    {
        return Assert::isInt(
            $this->createQueryBuilder('u')
                ->select('count(u.id)')
                ->where('u.roles=\'\'')
                ->getQuery()
                ->getSingleScalarResult()
        );
    }

    /**
     * @throws NoResultException|NonUniqueResultException
     */
    public function getUserCount(): int
    {
        return Assert::isInt(
            $this->createQueryBuilder('u')
                ->select('count(u.id)')
                ->getQuery()
                ->getSingleScalarResult()
        );
    }

    /**
     * @return User[]
     */
    public function findBySearchQuery(string $searchQuery, int $limit): array
    {
        $query = $this->createQueryBuilder('u')
            ->where('u.name LIKE :search or u.email LIKE :search')
            ->setParameter('search', addcslashes($searchQuery, '%_') . '%')
            ->orderBy('u.name', 'ASC')
            ->setMaxResults($limit)
            ->getQuery();

        /** @var User[] $result */
        $result = $query->getResult();

        return $result;
    }

    /**
     * @param int[] $userIds
     *
     * @return User[]
     */
    public function findUsersWithExclusion(array $userIds): array
    {
        $builder = $this->createQueryBuilder('u');
        if (count($userIds) > 0) {
            $builder->where($builder->expr()->notIn('u.id', $userIds));
        }

        /** @var User[] $result */
        $result = $builder->orderBy('u.name', 'ASC')->getQuery()->getResult();

        return $result;
    }
}
