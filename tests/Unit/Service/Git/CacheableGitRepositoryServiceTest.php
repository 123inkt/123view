<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Git;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Exception\RepositoryException;
use DR\Review\Model\Git\GitRepository;
use DR\Review\Service\Git\CacheableGitRepositoryService;
use DR\Review\Service\Git\GitCommandBuilderFactory;
use DR\Review\Service\Git\GitRepositoryFactory;
use DR\Review\Service\Git\GitRepositoryLocationService;
use DR\Review\Service\Git\Ssh\GitSshSetupService;
use DR\Review\Tests\AbstractTestCase;
use League\Uri\Uri;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;

#[CoversClass(CacheableGitRepositoryService::class)]
class CacheableGitRepositoryServiceTest extends AbstractTestCase
{
    private Filesystem&MockObject         $filesystem;
    private GitRepositoryFactory&MockObject $repositoryFactory;
    private CacheableGitRepositoryService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->filesystem        = $this->createMock(Filesystem::class);
        $this->repositoryFactory = $this->createMock(GitRepositoryFactory::class);
        $this->service           = new CacheableGitRepositoryService(
            static::createStub(LoggerInterface::class),
            $this->filesystem,
            null,
            static::createStub(GitRepositoryLocationService::class),
            static::createStub(GitSshSetupService::class),
        );
    }

    /**
     * @throws RepositoryException
     */
    public function testGetRepositoryWithoutCache(): void
    {
        $repository    = new Repository();
        $repository->setId(123);
        $repository->setUrl(Uri::new('https://my.repository.com'));
        $gitRepository = static::createStub(GitRepository::class);

        // setup mocks
        $this->filesystem->expects($this->once())->method('exists')->willReturn(true);
        $this->repositoryFactory->expects($this->once())->method('create')->willReturn($gitRepository);

        // first call should invoke parent method
        $firstRepository = $this->service->getRepository($repository);

        // second call should be from cache
        $secondRepository = $this->service->getRepository($repository);
        static::assertSame($firstRepository, $secondRepository);
    }
}
