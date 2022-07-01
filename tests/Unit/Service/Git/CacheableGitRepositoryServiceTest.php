<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Service\Git;

use CzProject\GitPhp\Git;
use CzProject\GitPhp\GitRepository;
use DR\GitCommitNotification\Exception\RepositoryException;
use DR\GitCommitNotification\Service\Git\CacheableGitRepositoryService;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Service\Git\CacheableGitRepositoryService
 */
class CacheableGitRepositoryServiceTest extends AbstractTestCase
{
    private const CACHE_DIRECTORY = "/cache/directory";

    /** @var MockObject|Filesystem */
    private Filesystem $filesystem;
    /** @var Git|MockObject */
    private Git                           $git;
    private CacheableGitRepositoryService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->git        = $this->createMock(Git::class);
        $this->filesystem = $this->createMock(Filesystem::class);
        $this->service    = new CacheableGitRepositoryService($this->log, $this->git, $this->filesystem, self::CACHE_DIRECTORY);
    }

    /**
     * @covers ::getRepository
     * @throws RepositoryException
     */
    public function testGetRepositoryWithoutCache(): void
    {
        $url        = 'http://my.repository.com';
        $repository = $this->createMock(GitRepository::class);

        // setup mocks
        $this->filesystem->expects(static::once())->method('exists')->willReturn(false);
        $this->git->expects(static::once())->method('init')->willReturn($repository);

        // first call should invoke parent method
        $firstRepository = $this->service->getRepository($url);

        // second call should be from cache
        $secondRepository = $this->service->getRepository($url);
        static::assertSame($firstRepository, $secondRepository);
    }
}
