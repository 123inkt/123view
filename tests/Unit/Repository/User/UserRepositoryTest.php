<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Repository\User;

use DR\Review\Repository\User\UserRepository;
use DR\Review\Security\Role\Roles;
use DR\Review\Tests\AbstractRepositoryTestCase;
use DR\Review\Tests\DataFixtures\UserFixtures;
use DR\Review\Utility\Assert;
use Exception;

/**
 * @coversDefaultClass \DR\Review\Repository\User\UserRepository
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

    /**
     * @covers ::getNewUserCount
     * @throws Exception
     */
    public function testGetNewUserCount(): void
    {
        $repository = self::getService(UserRepository::class);
        $user       = Assert::notNull($repository->findOneBy(['email' => 'sherlock@example.com']));

        // find one new user
        static::assertSame(1, $repository->getNewUserCount());

        // mark user as not new
        $user->setRoles([Roles::ROLE_USER]);
        $repository->save($user, true);

        static::assertSame(0, $repository->getNewUserCount());
    }

    /**
     * @covers ::getUserCount
     * @throws Exception
     */
    public function testGetUserCount(): void
    {
        $repository = self::getService(UserRepository::class);
        $user       = Assert::notNull($repository->findOneBy(['email' => 'sherlock@example.com']));

        static::assertSame(1, $repository->getUserCount());

        $repository->remove($user, true);
        static::assertSame(0, $repository->getUserCount());
    }

    /**
     * @inheritDoc
     */
    protected function getFixtures(): array
    {
        return [UserFixtures::class];
    }
}
