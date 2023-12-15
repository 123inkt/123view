<?php

declare(strict_types=1);

namespace DR\Review\Repository\User;

use Doctrine\Persistence\ManagerRegistry;
use DR\Review\Doctrine\EntityRepository\ServiceEntityRepository;
use DR\Review\Entity\User\GitAccessToken;

/**
 * @extends ServiceEntityRepository<GitAccessToken>
 * @method GitAccessToken|null find($id, $lockMode = null, $lockVersion = null)
 * @method GitAccessToken|null findOneBy(array $criteria, array $orderBy = null)
 * @method GitAccessToken[]    findAll()
 * @method GitAccessToken[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GitAccessTokenRepository extends ServiceEntityRepository
{
    /**
     * @codeCoverageIgnore
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GitAccessToken::class);
    }
}
