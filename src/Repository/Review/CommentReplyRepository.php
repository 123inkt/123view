<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Repository\Review;

use DR\GitCommitNotification\Entity\Review\CommentReply;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CommentReply>
 *
 * @method CommentReply|null find($id, $lockMode = null, $lockVersion = null)
 * @method CommentReply|null findOneBy(array $criteria, array $orderBy = null)
 * @method CommentReply[]    findAll()
 * @method CommentReply[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommentReplyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CommentReply::class);
    }

    public function save(CommentReply $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(CommentReply $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
