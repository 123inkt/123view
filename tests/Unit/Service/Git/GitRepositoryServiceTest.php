<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Service\Git;

use CzProject\GitPhp\Git;
use CzProject\GitPhp\GitException;
use CzProject\GitPhp\GitRepository;
use CzProject\GitPhp\RunnerResult;
use DR\GitCommitNotification\Exception\RepositoryException;
use DR\GitCommitNotification\Service\Git\GitRepositoryService;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Service\Git\GitRepositoryService
 * @covers ::__construct
 */
class GitRepositoryServiceTest extends AbstractTestCase
{
    private const CACHE_DIRECTORY = "/cache/directory";

    /** @var MockObject|Filesystem */
    private Filesystem $filesystem;
    /** @var Git|MockObject */
    private Git                  $git;
    private GitRepositoryService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->git        = $this->createMock(Git::class);
        $this->filesystem = $this->createMock(Filesystem::class);
        $this->service    = new GitRepositoryService($this->log, $this->git, $this->filesystem, self::CACHE_DIRECTORY);
    }

    /**
     * @covers ::getRepository
     * @covers ::tryGetRepository
     * @throws RepositoryException
     */
    public function testGetRepositoryWithoutCache(): void
    {
        $url        = 'http://my.repository.com';
        $repository = $this->createMock(GitRepository::class);

        // setup mocks
        $this->filesystem->expects(static::once())->method('mkdir')->with(self::CACHE_DIRECTORY . '/git/');
        $this->filesystem->expects(static::once())->method('exists')->willReturn(false);
        $this->git->expects(static::once())->method('init')->willReturn($repository);

        $repository->expects(static::once())->method('addRemote')->with('origin', $url);
        $repository->expects(static::once())->method('fetch')->with(null, ['--all']);

        $this->service->getRepository($url);
    }

    /**
     * @covers ::getRepository
     * @covers ::tryGetRepository
     * @throws RepositoryException
     */
    public function testGetRepositoryWithCache(): void
    {
        $url        = 'http://my.repository.com';
        $repository = $this->createMock(GitRepository::class);

        // setup mocks
        $this->filesystem->expects(static::once())->method('mkdir')->with(self::CACHE_DIRECTORY . '/git/');
        $this->filesystem->expects(static::once())->method('exists')->willReturn(true);
        $this->git->expects(static::once())->method('open')->willReturn($repository);

        $repository->expects(static::once())->method('fetch')->with(null, ['--all']);

        $this->service->getRepository($url);
    }

    /**
     * @covers ::getRepository
     * @covers ::tryGetRepository
     * @throws RepositoryException
     */
    public function testGetRepositoryWithException(): void
    {
        $url        = 'http://my.repository.com';
        $runnerResult = new RunnerResult('git', 1, ['output'], ['failure']);

        // setup mocks
        $this->filesystem->expects(static::once())->method('mkdir')->with(self::CACHE_DIRECTORY . '/git/');
        $this->filesystem->expects(static::once())->method('exists')->willReturn(true);
        $this->git->expects(static::once())->method('open')->willThrowException(new GitException('exception', 5, null, $runnerResult));

        $this->expectException(RepositoryException::class);
        $this->expectExceptionMessage('exception: failure');
        $this->service->getRepository($url);
    }
}
