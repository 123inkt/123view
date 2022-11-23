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
        static::assertCount(1, $users);
    }

    /**
     * @covers ::findUsersWithExclusion
     * @throws Exception
     */
    public function testFindUsersWithExclusion(): void
    {
        $repository = self::getService(UserRepository::class);

        $user = $repository->findOneBy(['email' => 'sherlock@example.com']);
        static::assertNotNull($user);

        static::assertCount(1, $repository->findUsersWithExclusion([]));
        static::assertCount(0, $repository->findUsersWithExclusion([(int)$user->getId()]));
    }

    protected function getFixtures(): array
    {
        return [UserFixtures::class];
    }
}
