<?php
declare(strict_types=1);

namespace DR\Review\Repository\Ai;

use Doctrine\Persistence\ManagerRegistry;
use DR\Review\Doctrine\EntityRepository\ServiceEntityRepository;
use DR\Review\Entity\Ai\AiReviewReferenceSection;

/**
 * @extends ServiceEntityRepository<AiReviewReferenceSection>
 * @method AiReviewReferenceSection|null find($id, $lockMode = null, $lockVersion = null)
 */
class AiReviewReferenceSectionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AiReviewReferenceSection::class);
    }
}
