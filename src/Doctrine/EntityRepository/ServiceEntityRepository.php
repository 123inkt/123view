<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Doctrine\EntityRepository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository as DoctrineServiceEntityRepository;

/**
 * @template T of object
 * @template-extends DoctrineServiceEntityRepository<T>
 */
class ServiceEntityRepository extends DoctrineServiceEntityRepository
{
    /**
     * @param T $entity
     */
    public function save(object $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @param T $entity
     */
    public function remove(object $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
