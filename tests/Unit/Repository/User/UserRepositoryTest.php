<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Repository\User;

use DR\GitCommitNotification\Repository\User\UserRepository;
use DR\GitCommitNotification\Tests\AbstractRepositoryTestCase;
use DR\GitCommitNotification\Tests\DataFixtures\UserFixtures;
use Exception;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Repository\User\UserRepository
 * @covers ::__construct
 */
class UserRepositoryTest extends AbstractRepositoryTestCase
{
    /**
     * @covers ::findBySearchQuery
     * @throws Exception
     */
    public function testFindBySearchQuery(): void
    {
        $repository = self::getService(UserRepository::class);
        $users      = $repository->findBySearchQuery('Sherlock', 10);
    }

    /**
     * @covers ::findUsersWithExclusion
     */
    public function testFindUsersWithExclusion(): void
    {
    }

    protected function getFixtures(): array
    {
        return [UserFixtures::class];
    }
}
