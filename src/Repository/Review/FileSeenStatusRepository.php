<?php

namespace DR\GitCommitNotification\Repository\Review;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use DR\GitCommitNotification\Entity\Review\FileSeenStatus;

/**
 * @extends ServiceEntityRepository<FileSeenStatus>
 * @method FileSeenStatus|null find($id, $lockMode = null, $lockVersion = null)
 * @method FileSeenStatus|null findOneBy(array $criteria, array $orderBy = null)
 * @method FileSeenStatus[]    findAll()
 * @method FileSeenStatus[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FileSeenStatusRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FileSeenStatus::class);
    }

    public function save(FileSeenStatus $entity, bool $flush = false): void
    {
        $em = $this->getEntityManager();
        $em->wrapInTransaction(function () use ($entity, $flush): void {
            $entityExists = $this->findOneBy(
                [
                    'review'   => (int)$entity->getReview()?->getId(),
                    'user'     => (int)$entity->getUser()?->getId(),
                    'filePath' => $entity->getFilePath()
                ]
            );
            if ($entityExists !== null) {
                return;
            }
            $this->getEntityManager()->persist($entity);
            if ($flush) {
                $this->getEntityManager()->flush();
            }
        });
    }

    public function remove(FileSeenStatus $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
