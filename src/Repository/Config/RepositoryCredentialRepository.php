<?php
declare(strict_types=1);

namespace DR\Review\Repository\Config;

use Doctrine\Persistence\ManagerRegistry;
use DR\Review\Doctrine\EntityRepository\ServiceEntityRepository;
use DR\Review\Entity\Repository\RepositoryCredential;

/**
 * @extends ServiceEntityRepository<RepositoryCredential>
 * @method RepositoryCredential|null find($id, $lockMode = null, $lockVersion = null)
 * @method RepositoryCredential|null findOneBy(array $criteria, array $orderBy = null)
 * @method RepositoryCredential[]    findAll()
 * @method RepositoryCredential[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RepositoryCredentialRepository extends ServiceEntityRepository
{
    /**
     * @codeCoverageIgnore
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RepositoryCredential::class);
    }
}
