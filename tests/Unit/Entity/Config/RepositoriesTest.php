<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Entity\Config;

use DR\GitCommitNotification\Entity\Config\Repositories;
use DR\GitCommitNotification\Entity\Config\Repository;
use DR\GitCommitNotification\Exception\ConfigException;
use DR\GitCommitNotification\Tests\AbstractTest;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Entity\Config\Repositories
 */
class RepositoriesTest extends AbstractTest
{
    /**
     * @covers ::getRepositories
     * @covers ::addRepository
     */
    public function testGetRepositories(): void
    {
        $repository = new Repository();

        $repositories = new Repositories();
        static::assertEmpty($repositories->getRepositories());

        $repositories->addRepository($repository);
        static::assertSame([$repository], $repositories->getRepositories());
    }

    /**
     * @covers ::getByReference
     * @throws ConfigException
     */
    public function testGetByName(): void
    {
        $repositoryA       = new Repository();
        $repositoryA->name = "repo-A";
        $repositoryB       = new Repository();
        $repositoryB->name = "repo-B";

        $repositories = new Repositories();
        $repositories->addRepository($repositoryA);
        $repositories->addRepository($repositoryB);
        static::assertSame($repositoryB, $repositories->getByReference($this->createRepositoryReference('repo-B')));
    }

    /**
     * @covers ::getByReference
     */
    public function testGetByNameShouldThrowErrorOnUnknown(): void
    {
        $this->expectException(ConfigException::class);
        $repositories = new Repositories();
        $repositories->getByReference($this->createRepositoryReference('foobar'));
    }
}
