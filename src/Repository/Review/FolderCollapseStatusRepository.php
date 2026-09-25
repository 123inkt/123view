<?php

declare(strict_types=1);

namespace DR\Review\Repository\Review;

use Doctrine\Persistence\ManagerRegistry;
use DR\Review\Doctrine\EntityRepository\ServiceEntityRepository;
use DR\Review\Entity\Review\FolderCollapseStatus;

/**
 * @extends ServiceEntityRepository<FolderCollapseStatus>
 * @method FolderCollapseStatus|null find($id, $lockMode = null, $lockVersion = null)
 * @method FolderCollapseStatus|null findOneBy(array $criteria, array $orderBy = null)
 * @method FolderCollapseStatus[]    findAll()
 * @method FolderCollapseStatus[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FolderCollapseStatusRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FolderCollapseStatus::class);
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
                    'review' => (int)$entity->getReview()->getId(),
                    'user'   => (int)$entity->getUser()->getId(),
                    'path'   => $entity->getPath()
                ]
            );
            if ($entityExists !== null) {
                return;
            }
            parent::save($entity, $flush);
        });
    }
}
