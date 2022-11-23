<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Repository\Review;

use Doctrine\Persistence\ManagerRegistry;
use DR\GitCommitNotification\Doctrine\EntityRepository\ServiceEntityRepository;
use DR\GitCommitNotification\Entity\Review\CommentReply;

/**
 * @extends ServiceEntityRepository<CommentReply>
 * @method CommentReply|null find($id, $lockMode = null, $lockVersion = null)
 * @method CommentReply|null findOneBy(array $criteria, array $orderBy = null)
 * @method CommentReply[]    findAll()
 * @method CommentReply[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommentReplyRepository extends ServiceEntityRepository
{
    /**
     * @codeCoverageIgnore
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CommentReply::class);
    }
}
