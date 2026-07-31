<?php
declare(strict_types=1);

namespace DR\Review\Repository\Ai;

use Doctrine\Persistence\ManagerRegistry;
use DR\Review\Doctrine\EntityRepository\ServiceEntityRepository;
use DR\Review\Entity\Ai\AiReviewReferenceMatcher;

/**
 * @extends ServiceEntityRepository<AiReviewReferenceMatcher>
 * @method AiReviewReferenceMatcher|null find($id, $lockMode = null, $lockVersion = null)
 */
class AiReviewReferenceMatcherRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AiReviewReferenceMatcher::class);
    }
}
