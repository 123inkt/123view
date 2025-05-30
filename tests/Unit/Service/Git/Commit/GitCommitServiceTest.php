<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Git\Commit;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Exception\RepositoryException;
use DR\Review\Git\GitRepository;
use DR\Review\Service\Git\CacheableGitRepositoryService;
use DR\Review\Service\Git\Commit\GitCommitCommandBuilder;
use DR\Review\Service\Git\Commit\GitCommitService;
use DR\Review\Service\Git\GitCommandBuilderFactory;
use DR\Review\Tests\AbstractTestCase;
use League\Uri\Uri;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(GitCommitService::class)]
class GitCommitServiceTest extends AbstractTestCase
{
    private CacheableGitRepositoryService&MockObject $repositoryService;
    private GitCommandBuilderFactory&MockObject      $builderFactory;
    private GitCommitService                         $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repositoryService = $this->createMock(CacheableGitRepositoryService::class);
        $this->builderFactory    = $this->createMock(GitCommandBuilderFactory::class);
        $this->service           = new GitCommitService($this->repositoryService, $this->builderFactory);
    }

    /**
     * @throws RepositoryException
     */
    public function testCommit(): void
    {
        $message = 'foobar';

        $repository = new Repository();
        $repository->setUrl(Uri::new('https://url/'));

        $builder = $this->createMock(GitCommitCommandBuilder::class);
        $builder->expects($this->once())->method('message')->with($message)->willReturnSelf();
        $builder->expects($this->once())->method('allowEmpty')->willReturnSelf();
        $this->builderFactory->expects($this->once())->method('createCommit')->willReturn($builder);

        $git = $this->createMock(GitRepository::class);
        $git->expects($this->once())->method('execute')->with($builder)->willReturn('output');
        $this->repositoryService->expects($this->once())->method('getRepository')->with($repository)->willReturn($git);

        $this->service->commit($repository, $message);
    }
}
