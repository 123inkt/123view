<?php
declare(strict_types=1);

namespace DR\Review\Doctrine\EntityRepository;

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
     * @param array<string, int|float|string|int[]|string[]|object> $criteria
     * @param array<string, string>|null                            $orderBy
     */
    public function removeOneBy(array $criteria, ?array $orderBy = null, bool $flush = false): void
    {
        $entity = $this->findOneBy($criteria, $orderBy);
        if ($entity === null) {
            return;
        }

        $this->remove($entity, $flush);
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
