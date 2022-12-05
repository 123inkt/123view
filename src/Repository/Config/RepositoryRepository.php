<?php
declare(strict_types=1);

namespace DR\Review\Repository\Config;

use Doctrine\Persistence\ManagerRegistry;
use DR\Review\Doctrine\EntityRepository\ServiceEntityRepository;
use DR\Review\Entity\Repository\Repository;

/**
 * @extends ServiceEntityRepository<Repository>
 * @method Repository|null find($id, $lockMode = null, $lockVersion = null)
 * @method Repository|null findOneBy(array $criteria, array $orderBy = null)
 * @method Repository[]    findAll()
 * @method Repository[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RepositoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Repository::class);
    }

    /**
     * @return Repository[]
     */
    public function findByUpdateRevisions(): array
    {
        $qb = $this->createQueryBuilder('r');
        $qb
            ->where('r.active = 1')
            ->andWhere(
                'r.updateRevisionsTimestamp + r.updateRevisionsInterval < :currentTime' .
                ' OR ' .
                'r.updateRevisionsTimestamp IS NULL'
            )
            ->setParameter('currentTime', time());

        /** @var Repository[] $result */
        $result = $qb->getQuery()->getResult();

        return $result;
    }
}
