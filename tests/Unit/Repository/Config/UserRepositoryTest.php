<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Repository\Config;

use DR\GitCommitNotification\Entity\Config\ExternalLink;
use DR\GitCommitNotification\Entity\Config\User;
use DR\GitCommitNotification\Repository\Config\ExternalLinkRepository;
use DR\GitCommitNotification\Repository\Config\UserRepository;
use DR\GitCommitNotification\Tests\AbstractRepositoryTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Repository\Config\UserRepository
 * @covers ::__construct
 */
class UserRepositoryTest extends AbstractRepositoryTestCase
{
    /** @var UserRepository&MockObject */
    private UserRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = $this->getRepository(UserRepository::class);
    }

    /**
     * @covers ::add
     */
    public function testAdd(): void
    {
        $user = new User();

        $this->expectPersist($user);
        $this->expectFlush();
        $this->repository->add($user, true);
    }
}
