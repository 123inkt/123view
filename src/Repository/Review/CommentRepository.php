<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Repository\Review;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use DR\GitCommitNotification\Entity\Review\CodeReview;
use DR\GitCommitNotification\Entity\Review\Comment;

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

    public function save(Comment $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Comment $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return Comment[]
     */
    public function findByReview(CodeReview $review, string $filePath): array
    {
        $qb = $this->createQueryBuilder('c')
            ->where('c.review = :reviewId')
            ->andWhere('c.filePath = :filePath')
            ->setParameter('reviewId', $review->getId())
            ->setParameter('filePath', $filePath)
            ->orderBy('c.id', 'ASC');

        /** @var Comment[] $result */
        $result = $qb->getQuery()->getResult();

        return $result;
    }
}
