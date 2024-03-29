<?php
declare(strict_types=1);

namespace DR\Review\Repository\Review;

use Doctrine\Persistence\ManagerRegistry;
use DR\Review\Doctrine\EntityRepository\ServiceEntityRepository;
use DR\Review\Entity\Review\CodeReviewer;

/**
 * @extends ServiceEntityRepository<CodeReviewer>
 * @method CodeReviewer|null find($id, $lockMode = null, $lockVersion = null)
 * @method CodeReviewer|null findOneBy(array $criteria, array $orderBy = null)
 * @method CodeReviewer[]    findAll()
 * @method CodeReviewer[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CodeReviewerRepository extends ServiceEntityRepository
{
    /**
     * @codeCoverageIgnore
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CodeReviewer::class);
    }
}
