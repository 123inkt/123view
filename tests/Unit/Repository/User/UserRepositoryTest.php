<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Repository\User;

use DR\GitCommitNotification\Entity\User\User;
use DR\GitCommitNotification\Repository\User\UserRepository;
use DR\GitCommitNotification\Tests\AbstractRepositoryTestCase;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Repository\User\UserRepository
 * @covers ::__construct
 */
class UserRepositoryTest extends AbstractRepositoryTestCase
{
    private UserRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = $this->getRepository(UserRepository::class);
    }

    /**
     * @covers ::save
     */
    public function testAdd(): void
    {
        $user = new User();

        $this->expectPersist($user);
        $this->expectFlush();
        $this->repository->save($user, true);
    }

    protected function getRepositoryEntityClassString(): string
    {
        return User::class;
    }
}
