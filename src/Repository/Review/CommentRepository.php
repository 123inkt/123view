<?php
declare(strict_types=1);

namespace DR\Review\Repository\Review;

use Doctrine\DBAL\Exception as DBALException;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Query\UnionType;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use DR\Review\Doctrine\EntityRepository\ServiceEntityRepository;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\CommentReply;
use DR\Review\Entity\Review\CommentTypeEnum;
use DR\Review\Entity\User\User;
use DR\Utils\Assert;

/**
 * @extends ServiceEntityRepository<Comment>
 * @method Comment|null find($id, $lockMode = null, $lockVersion = null)
 * @method Comment|null findOneBy(array $criteria, array $orderBy = null)
 * @method Comment[]    findAll()
 * @method Comment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommentRepository extends ServiceEntityRepository
{
    public const string ORDER_CREATE_TIMESTAMP = 'create-timestamp';
    public const string ORDER_UPDATE_TIMESTAMP = 'update-timestamp';

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Comment::class);
    }

    /**
     * @param string[] $filePaths
     *
     * @return Comment[]
     */
    public function findByReview(CodeReview $review, array $filePaths): array
    {
        $qb = $this->createQueryBuilder('c')
            ->where('c.review = :reviewId')
            ->andWhere('c.filePath IN (:filePaths)')
            ->setParameter('reviewId', $review->getId())
            ->setParameter('filePaths', $filePaths)
            ->orderBy('c.id', 'ASC');

        /** @var Comment[] $result */
        $result = $qb->getQuery()->getResult();

        return $result;
    }

    /**
     * Returns all draft comments for the given user, ordered by review then comment id.
     * JOIN fetches review and repository to avoid N+1 queries.
     *
     * @param int<1, max> $page
     *
     * @return Paginator<Comment>
     */
    public function getDraftsByUser(User $user, int $page, int $perPage = 30): Paginator
    {
        $qb = $this->createQueryBuilder('c')
            ->addSelect('r', 'repo')
            ->innerJoin('c.review', 'r')
            ->innerJoin('r.repository', 'repo')
            ->where('c.user = :user')
            ->andWhere('c.type = :type')
            ->setParameter('user', $user)
            ->setParameter('type', CommentTypeEnum::Draft)
            ->orderBy('c.id', 'DESC')
            ->setFirstResult(($page - 1) * $perPage)
            ->setMaxResults($perPage);

        /** @phpstan-var Paginator<Comment> */
        return new Paginator($qb->getQuery(), false);
    }

    /**
     * Count all draft comments for the given user.
     */
    public function countDraftsByUser(User $user): int
    {
        return Assert::integer(
            $this->createQueryBuilder('c')
                ->select('COUNT(c.id)')
                ->where('c.user = :user')
                ->andWhere('c.type = :type')
                ->setParameter('user', $user)
                ->setParameter('type', CommentTypeEnum::Draft)
                ->getQuery()
                ->getSingleScalarResult()
        );
    }

    /**
     * Returns the comments and replies placed by the given user, ordered by create or update timestamp descending.
     * JOIN fetches review and repository (and for replies the comment and its author) to avoid N+1 queries.
     *
     * @param int<1, max> $page
     *
     * @return list<Comment|CommentReply>
     * @throws DBALException
     */
    public function findCommentsAndRepliesByUser(User $user, string $searchQuery, string $orderBy, int $page, int $perPage = 30): array
    {
        $orderColumn = $orderBy === self::ORDER_UPDATE_TIMESTAMP ? 'update_timestamp' : 'create_timestamp';

        /** @var list<array{type: 'comment'|'reply', id: int|numeric-string}> $rows */
        $rows = $this->createCommentsAndRepliesQuery($user, $searchQuery)
            ->orderBy($orderColumn, 'DESC')
            ->addOrderBy('id', 'DESC')
            ->addOrderBy('type', 'ASC')
            ->setFirstResult(($page - 1) * $perPage)
            ->setMaxResults($perPage)
            ->executeQuery()
            ->fetchAllAssociative();

        $ids = ['comment' => [], 'reply' => []];
        foreach ($rows as $row) {
            $ids[$row['type']][] = (int)$row['id'];
        }

        $entities = ['comment' => $this->findCommentsByIds($ids['comment']), 'reply' => $this->findRepliesByIds($ids['reply'])];

        $result = [];
        foreach ($rows as $row) {
            // skip the entity when it was removed in between both queries
            $entity = $entities[$row['type']][(int)$row['id']] ?? null;
            if ($entity !== null) {
                $result[] = $entity;
            }
        }

        return $result;
    }

    /**
     * Count all comments and replies placed by the given user.
     * @throws DBALException
     */
    public function countCommentsAndRepliesByUser(User $user, string $searchQuery): int
    {
        $query = $this->createCommentsAndRepliesQuery($user, $searchQuery);
        $count = $this->getEntityManager()
            ->getConnection()
            ->executeQuery(sprintf('SELECT COUNT(*) FROM (%s) AS `items`', $query->getSQL()), $query->getParameters(), $query->getParameterTypes())
            ->fetchOne();

        return (int)Assert::numeric($count);
    }

    /**
     * Create the UNION query for all comments and replies of the given user, optionally filtered by message. Replies on draft
     * comments of other users are excluded, as these comments are not visible to the user.
     * @throws DBALException
     */
    private function createCommentsAndRepliesQuery(User $user, string $searchQuery): QueryBuilder
    {
        $connection = $this->getEntityManager()->getConnection();

        $comments = $connection->createQueryBuilder()
            ->select("'comment' AS type", 'c.id', 'c.create_timestamp', 'c.update_timestamp')
            ->from('comment', 'c')
            ->where('c.user_id = :userId');

        $replies = $connection->createQueryBuilder()
            ->select("'reply' AS type", 'r.id', 'r.create_timestamp', 'r.update_timestamp')
            ->from('comment_reply', 'r')
            ->innerJoin('r', 'comment', 'c', 'c.id = r.comment_id')
            ->where('r.user_id = :userId')
            ->andWhere('c.type = :finalType OR c.user_id = :userId');

        $query = $connection->createQueryBuilder()
            ->setParameter('userId', $user->getId(), ParameterType::INTEGER)
            ->setParameter('finalType', CommentTypeEnum::Final->value);

        if ($searchQuery !== '') {
            $comments->andWhere("c.message LIKE :search ESCAPE '!'");
            $replies->andWhere("r.message LIKE :search ESCAPE '!'");
            $query->setParameter('search', '%' . $connection->getDatabasePlatform()->escapeStringForLike($searchQuery, '!') . '%');
        }

        return $query->union($comments)->addUnion($replies, UnionType::ALL);
    }

    /**
     * @param int[] $ids
     *
     * @return array<int, Comment>
     */
    private function findCommentsByIds(array $ids): array
    {
        /** @var array<int, Comment> $result */
        $result = $this->createQueryBuilder('c', 'c.id')
            ->addSelect('r', 'repo')
            ->innerJoin('c.review', 'r')
            ->innerJoin('r.repository', 'repo')
            ->where('c.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->getResult();

        return $result;
    }

    /**
     * @param int[] $ids
     *
     * @return array<int, CommentReply>
     */
    private function findRepliesByIds(array $ids): array
    {
        /** @var array<int, CommentReply> $result */
        $result = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('cr', 'c', 'u', 'r', 'repo')
            ->from(CommentReply::class, 'cr', 'cr.id')
            ->innerJoin('cr.comment', 'c')
            ->innerJoin('c.user', 'u')
            ->innerJoin('c.review', 'r')
            ->innerJoin('r.repository', 'repo')
            ->where('cr.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->getResult();

        return $result;
    }
}
