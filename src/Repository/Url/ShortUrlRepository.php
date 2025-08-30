<?php
declare(strict_types=1);

namespace DR\Review\Repository\Url;

use Doctrine\Persistence\ManagerRegistry;
use DR\Review\Doctrine\EntityRepository\ServiceEntityRepository;
use DR\Review\Entity\Url\ShortUrl;

/**
 * @extends ServiceEntityRepository<ShortUrl>
 */
class ShortUrlRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ShortUrl::class);
    }
}