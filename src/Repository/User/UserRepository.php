<?php
declare(strict_types=1);

namespace DR\Review\Repository\User;

use Doctrine\DBAL\Exception;
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
        $builder->select(['u', 's']);
        $builder->leftJoin('u.setting', 's');
        if (count($userIds) > 0) {
            $builder->where($builder->expr()->notIn('u.id', $userIds));
        }

        /** @var User[] $result */
        $result = $builder->orderBy('u.name', 'ASC')->getQuery()->getResult();

        return $result;
    }

    /**
     * @return User[]
     * @throws Exception
     */
    public function getActors(int $reviewId): array
    {
        // get all users ids for:
        //    authors, reviewers, commenters, repliers and user mentions
        $query = $this->getEntityManager()->getConnection()->prepare(
            "SELECT `actor`.user_id
             FROM (
              ( SELECT u.id AS user_id FROM revision INNER JOIN `user` u ON revision.author_email=u.email WHERE revision.review_id=:reviewId )
              UNION
              ( SELECT reviewer.user_id FROM code_reviewer reviewer WHERE reviewer.review_id=:reviewId )
              UNION
              ( SELECT comment.user_id FROM `comment` WHERE comment.review_id=:reviewId )
              UNION
              ( SELECT rply.user_id FROM `comment_reply` rply INNER JOIN `comment` ON rply.comment_id=comment.id WHERE comment.review_id=:reviewId )
              UNION
              ( SELECT um.user_id FROM `user_mention` um INNER JOIN `comment` ON um.comment_id=comment.id WHERE comment.review_id=:reviewId )
             ) AS `actor`
             WHERE `actor`.user_id IS NOT NULL
             GROUP BY `actor`.user_id"
        );
        $query->bindValue('reviewId', $reviewId);
        $userIds = $query->executeQuery()->fetchFirstColumn();

        return count($userIds) === 0 ? [] : $this->findBy(['id' => $userIds]);
    }
}
