<?php
declare(strict_types=1);

namespace DR\Review\Repository\Review;

use Doctrine\Persistence\ManagerRegistry;
use DR\Review\Doctrine\EntityRepository\ServiceEntityRepository;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\Comment;

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
}
