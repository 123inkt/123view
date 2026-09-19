<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Git;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Model\Git\GitRepository;
use DR\Review\Service\Git\GitRepositoryFactory;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Stopwatch\Stopwatch;

#[CoversClass(GitRepositoryFactory::class)]
class GitRepositoryFactoryTest extends AbstractTestCase
{
    public function testCreateWithStopwatch(): void
    {
        $stopwatch  = new Stopwatch();
        $repository = new Repository();
        $factory    = new GitRepositoryFactory($this->logger, $stopwatch);

        $gitRepository = $factory->create($repository, '/repo/path/');
        $expected      = new GitRepository($this->logger, $repository, $stopwatch, '/repo/path/');
        static::assertEquals($expected, $gitRepository);
    }
}
