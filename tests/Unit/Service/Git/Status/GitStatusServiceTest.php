<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Git\Status;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Exception\RepositoryException;
use DR\Review\Git\GitRepository;
use DR\Review\Service\Git\CacheableGitRepositoryService;
use DR\Review\Service\Git\GitCommandBuilderFactory;
use DR\Review\Service\Git\Status\GitStatusCommandBuilder;
use DR\Review\Service\Git\Status\GitStatusService;
use DR\Review\Tests\AbstractTestCase;
use League\Uri\Uri;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(GitStatusService::class)]
class GitStatusServiceTest extends AbstractTestCase
{
    private CacheableGitRepositoryService&MockObject $repositoryService;
    private GitCommandBuilderFactory&MockObject      $builderFactory;
    private GitStatusService                         $service;

    public function setUp(): void
    {
        parent::setUp();
        $this->repositoryService = $this->createMock(CacheableGitRepositoryService::class);
        $this->builderFactory    = $this->createMock(GitCommandBuilderFactory::class);
        $this->service           = new GitStatusService($this->repositoryService, $this->builderFactory);
    }

    /**
     * @throws RepositoryException
     */
    public function testGetModifiedFiles(): void
    {
        $output = "AB src/file/a\nCD src/file/b\n";

        $repository = new Repository();
        $repository->setUrl(Uri::new('https://example.com'));

        $builder = $this->createMock(GitStatusCommandBuilder::class);
        $builder->expects($this->once())->method('porcelain')->willReturnSelf();
        $this->builderFactory->expects($this->once())->method('createStatus')->willReturn($builder);

        $gitRepository = $this->createMock(GitRepository::class);
        $gitRepository->expects(static::once())->method('execute')->with($builder)->willReturn($output);
        $this->repositoryService->expects(static::once())->method('getRepository')->with($repository)->willReturn($gitRepository);

        $files = $this->service->getModifiedFiles($repository);
        static::assertSame(['src/file/a', 'src/file/b'], $files);
    }
}
