<?php
declare(strict_types=1);

namespace DR\Review\Repository\Review;

use Doctrine\Persistence\ManagerRegistry;
use DR\Review\Doctrine\EntityRepository\ServiceEntityRepository;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\UserMention;

/**
 * @extends ServiceEntityRepository<UserMention>
 * @method UserMention|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserMention|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserMention[]    findAll()
 * @method UserMention[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserMentionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserMention::class);
    }

    /**
     * @param UserMention[] $mentions
     */
    public function saveAll(Comment $comment, array $mentions): void
    {
        $em = $this->getEntityManager();
        $em->wrapInTransaction(function () use ($comment, $mentions): void {
            foreach ($comment->getMentions() as $mention) {
                $this->remove($mention);
            }
            $comment->getMentions()->clear();
            $this->getEntityManager()->persist($comment);
            $this->getEntityManager()->flush();

            foreach ($mentions as $mention) {
                $comment->getMentions()->add($mention);
                $this->save($mention);
            }

            $this->getEntityManager()->persist($comment);
            $this->getEntityManager()->flush();
        });
    }
}
