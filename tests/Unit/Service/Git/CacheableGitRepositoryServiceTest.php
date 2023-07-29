<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Git;

use CzProject\GitPhp\Git;
use CzProject\GitPhp\GitRepository;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Exception\RepositoryException;
use DR\Review\Service\Git\CacheableGitRepositoryService;
use DR\Review\Tests\AbstractTestCase;
use League\Uri\Uri;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @coversDefaultClass \DR\Review\Service\Git\CacheableGitRepositoryService
 */
class CacheableGitRepositoryServiceTest extends AbstractTestCase
{
    private const CACHE_DIRECTORY = "/cache/directory";

    private Filesystem&MockObject         $filesystem;
    private Git&MockObject                $git;
    private CacheableGitRepositoryService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->git        = $this->createMock(Git::class);
        $this->filesystem = $this->createMock(Filesystem::class);
        $this->service    = new CacheableGitRepositoryService(
            $this->createMock(LoggerInterface::class),
            $this->git,
            $this->filesystem,
            null,
            self::CACHE_DIRECTORY
        );
    }

    /**
     * @covers ::getRepository
     * @throws RepositoryException
     */
    public function testGetRepositoryWithoutCache(): void
    {
        $repository = new Repository();
        $repository->setId(123);
        $repository->setUrl(Uri::createFromString('http://my.repository.com'));
        $gitRepository = $this->createMock(GitRepository::class);

        // setup mocks
        $this->filesystem->expects(static::once())->method('exists')->willReturn(false);
        $this->git->expects(static::once())->method('cloneRepository')->willReturn($gitRepository);

        // first call should invoke parent method
        $firstRepository = $this->service->getRepository($repository);

        // second call should be from cache
        $secondRepository = $this->service->getRepository($repository);
        static::assertSame($firstRepository, $secondRepository);
    }
}
