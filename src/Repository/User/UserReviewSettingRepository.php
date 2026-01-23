<?php
declare(strict_types=1);

namespace DR\Review\Repository\User;

use Doctrine\Persistence\ManagerRegistry;
use DR\Review\Doctrine\EntityRepository\ServiceEntityRepository;
use DR\Review\Entity\User\UserReviewSetting;

/**
 * @extends ServiceEntityRepository<UserReviewSetting>
 * @method UserReviewSetting|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserReviewSetting|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserReviewSetting[]    findAll()
 * @method UserReviewSetting[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserReviewSettingRepository extends ServiceEntityRepository
{
    /**
     * @codeCoverageIgnore
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserReviewSetting::class);
    }
}
