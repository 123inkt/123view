<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Repository\User;

use Doctrine\ORM\Query\Expr\Func;
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
    public function testSave(): void
    {
        $user = new User();

        $this->expectPersist($user);
        $this->expectFlush();
        $this->repository->save($user, true);
    }

    /**
     * @covers ::findBySearchQuery
     */
    public function testFindBySearchQuery(): void
    {
        $user = new User();
        $this->expectCreateQueryBuilder('u')
            ->where('u.name LIKE :search or u.email LIKE :search')
            ->setParameter('search', '\%search\%%')
            ->orderBy('u.name', 'ASC')
            ->setMaxResults(20)
            ->getResult([$user]);

        static::assertSame([$user], $this->repository->findBySearchQuery("%search%", 20));
    }

    /**
     * @covers ::findUsersWithExclusion
     */
    public function testFindUsersWithExclusion(): void
    {
        $user = new User();
        $this->expectCreateQueryBuilder('u')
            ->where(new Func('u.id NOT IN', [5]))
            ->orderBy('u.name', 'ASC')
            ->getResult([$user]);

        static::assertSame([$user], $this->repository->findUsersWithExclusion([5]));
    }

    protected function getRepositoryEntityClassString(): string
    {
        return User::class;
    }
}
