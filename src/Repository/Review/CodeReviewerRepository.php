<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Repository\Review;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use DR\GitCommitNotification\Entity\Review\CodeReviewer;

/**
 * @extends ServiceEntityRepository<CodeReviewer>
 *
 * @method CodeReviewer|null find($id, $lockMode = null, $lockVersion = null)
 * @method CodeReviewer|null findOneBy(array $criteria, array $orderBy = null)
 * @method CodeReviewer[]    findAll()
 * @method CodeReviewer[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CodeReviewerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CodeReviewer::class);
    }

    public function save(CodeReviewer $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(CodeReviewer $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
