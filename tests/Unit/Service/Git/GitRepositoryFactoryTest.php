<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Git;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Git\GitRepository;
use DR\Review\Service\Git\GitRepositoryFactory;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Stopwatch\Stopwatch;

#[CoversClass(GitRepositoryFactory::class)]
class GitRepositoryFactoryTest extends AbstractTestCase
{
    public function testCreateWithoutStopwatch(): void
    {
        $repository = new Repository();
        $factory    = new GitRepositoryFactory($this->logger, null);

        static::assertInstanceOf(GitRepository::class, $factory->create($repository, '/repo/path/'));
    }

    public function testCreateWithStopwatch(): void
    {
        $repository = new Repository();
        $factory    = new GitRepositoryFactory($this->logger, new Stopwatch());

        static::assertInstanceOf(GitRepository::class, $factory->create($repository, '/repo/path/'));
    }
}
