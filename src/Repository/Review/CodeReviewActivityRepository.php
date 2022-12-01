<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Repository\Review;

use DR\GitCommitNotification\Doctrine\EntityRepository\ServiceEntityRepository;
use DR\GitCommitNotification\Entity\Review\CodeReviewActivity;

/**
 * @extends ServiceEntityRepository<CodeReviewActivity>
 * @method CodeReviewActivity|null find($id, $lockMode = null, $lockVersion = null)
 * @method CodeReviewActivity|null findOneBy(array $criteria, array $orderBy = null)
 * @method CodeReviewActivity[]    findAll()
 * @method CodeReviewActivity[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CodeReviewActivityRepository extends ServiceEntityRepository
{
}
