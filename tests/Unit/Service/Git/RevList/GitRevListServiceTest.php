<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Git\RevList;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Exception\RepositoryException;
use DR\Review\Git\GitRepository;
use DR\Review\Service\Git\CacheableGitRepositoryService;
use DR\Review\Service\Git\GitCommandBuilderFactory;
use DR\Review\Service\Git\RevList\GitRevListCommandBuilder;
use DR\Review\Service\Git\RevList\GitRevListParser;
use DR\Review\Service\Git\RevList\GitRevListService;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(GitRevListService::class)]
class GitRevListServiceTest extends AbstractTestCase
{
    private CacheableGitRepositoryService&MockObject $repositoryService;
    private GitCommandBuilderFactory&MockObject      $commandFactory;
    private GitRevListParser&MockObject              $revListParser;
    private GitRevListService                        $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repositoryService = $this->createMock(CacheableGitRepositoryService::class);
        $this->commandFactory    = $this->createMock(GitCommandBuilderFactory::class);
        $this->revListParser     = $this->createMock(GitRevListParser::class);
        $this->service           = new GitRevListService($this->repositoryService, $this->commandFactory, $this->revListParser);
    }

    /**
     * @throws RepositoryException
     */
    public function testGetCommitsAheadOfMaster(): void
    {
        $repository = new Repository();
        $repository->setMainBranchName('master');

        $builder       = $this->createMock(GitRevListCommandBuilder::class);
        $gitRepository = $this->createMock(GitRepository::class);

        $builder->expects(self::once())->method('commitRange')->with('origin/master', 'branch_name')->willReturnSelf();
        $builder->expects(self::once())->method('leftRight')->willReturnSelf();
        $builder->expects(self::once())->method('noMerges')->willReturnSelf();
        $builder->expects(self::once())->method('pretty')->with('oneline')->willReturnSelf();
        $builder->expects(self::once())->method('rightOnly')->willReturnSelf();
        $this->commandFactory->expects(self::once())->method('createRevList')->willReturn($builder);
        $this->repositoryService->expects(self::once())->method('getRepository')->with($repository)->willReturn($gitRepository);
        $gitRepository->expects(self::once())->method('execute')->with($builder)->willReturn('output');
        $this->revListParser->expects(self::once())->method('parseOneLine')->with('output')->willReturn(['result']);

        static::assertSame(['result'], $this->service->getCommitsAheadOfMaster($repository, 'branch_name'));
    }
}
