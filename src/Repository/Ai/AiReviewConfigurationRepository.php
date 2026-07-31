<?php
declare(strict_types=1);

namespace DR\Review\Repository\Ai;

use Doctrine\Persistence\ManagerRegistry;
use DR\Review\Doctrine\EntityRepository\ServiceEntityRepository;
use DR\Review\Entity\Ai\AiReviewConfiguration;

/**
 * @extends ServiceEntityRepository<AiReviewConfiguration>
 * @method AiReviewConfiguration|null find($id, $lockMode = null, $lockVersion = null)
 */
class AiReviewConfigurationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AiReviewConfiguration::class);
    }

    public function getSingleton(): AiReviewConfiguration
    {
        return $this->find(AiReviewConfiguration::SINGLETON_ID) ?? new AiReviewConfiguration();
    }
}
