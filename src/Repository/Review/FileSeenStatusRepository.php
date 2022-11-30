<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Repository\Review;

use Doctrine\Persistence\ManagerRegistry;
use DR\GitCommitNotification\Doctrine\EntityRepository\ServiceEntityRepository;
use DR\GitCommitNotification\Entity\Git\Diff\DiffFile;
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

    /**
     * @inheritDoc
     */
    public function save(object $entity, bool $flush = false): void
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
            parent::save($entity, $flush);
        });
    }

    /**
     * @param DiffFile[] $files
     */
    public function markAsUnseen(array $files): void
    {
    }
}
