<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Repository\User;

use Doctrine\Persistence\ManagerRegistry;
use DR\GitCommitNotification\Doctrine\EntityRepository\ServiceEntityRepository;
use DR\GitCommitNotification\Entity\User\UserSetting;

/**
 * @extends ServiceEntityRepository<UserSetting>
 * @method UserSetting|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserSetting|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserSetting[]    findAll()
 * @method UserSetting[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserSettingRepository extends ServiceEntityRepository
{
    /**
     * @codeCoverageIgnore
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserSetting::class);
    }
}
