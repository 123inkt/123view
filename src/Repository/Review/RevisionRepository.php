<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Repository\Review;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use DR\GitCommitNotification\Entity\Review\Revision;

/**
 * @extends ServiceEntityRepository<Revision>
 * @method Revision|null find($id, $lockMode = null, $lockVersion = null)
 * @method Revision|null findOneBy(array $criteria, array $orderBy = null)
 * @method Revision[]    findAll()
 * @method Revision[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RevisionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Revision::class);
    }

    public function save(Revision $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Revision $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function flush(): void
    {
        $this->getEntityManager()->flush();
    }
}
