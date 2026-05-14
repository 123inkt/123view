<?php
declare(strict_types=1);

namespace DR\Review\Repository\Review;

use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use DR\Review\Doctrine\EntityRepository\ServiceEntityRepository;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\Comment;
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

        /** @phpstan-var Paginator<Comment> $paginator */
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
}
