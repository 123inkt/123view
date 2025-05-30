<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Git\Fetch;

use DR\Review\Entity\Git\Fetch\BranchUpdate;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Git\GitRepository;
use DR\Review\Service\Git\Fetch\GitFetchCommandBuilder;
use DR\Review\Service\Git\Fetch\GitFetchService;
use DR\Review\Service\Git\GitCommandBuilderFactory;
use DR\Review\Service\Git\GitRepositoryService;
use DR\Review\Service\Parser\Fetch\GitFetchParser;
use DR\Review\Tests\AbstractTestCase;
use Exception;
use League\Uri\Uri;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(GitFetchService::class)]
class GitFetchServiceTest extends AbstractTestCase
{
    private GitCommandBuilderFactory&MockObject $commandBuilderFactory;
    private GitFetchParser&MockObject           $fetchParser;
    private GitRepositoryService&MockObject     $repositoryService;
    private GitFetchService                     $fetchService;

    public function setUp(): void
    {
        parent::setUp();
        $this->commandBuilderFactory = $this->createMock(GitCommandBuilderFactory::class);
        $this->fetchParser           = $this->createMock(GitFetchParser::class);
        $this->repositoryService     = $this->createMock(GitRepositoryService::class);
        $this->fetchService          = new GitFetchService(
            $this->commandBuilderFactory,
            $this->fetchParser,
            $this->repositoryService
        );
    }

    /**
     * @throws Exception
     */
    public function testFetch(): void
    {
        $repository = new Repository();
        $repository->setId(123);
        $repository->setUrl(Uri::new('https://www.example.com'));
        $change = new BranchUpdate('from', 'to', 'oldBranch', 'newBranch');

        $gitRepository = $this->createMock(GitRepository::class);

        $fetchBuilder = $this->createMock(GitFetchCommandBuilder::class);
        $fetchBuilder->expects($this->once())->method('all')->willReturnSelf();
        $fetchBuilder->expects($this->once())->method('verbose')->willReturnSelf();
        $fetchBuilder->expects($this->once())->method('noTags')->willReturnSelf();
        $fetchBuilder->expects($this->once())->method('prune')->willReturnSelf();

        $this->commandBuilderFactory->expects($this->once())->method('createFetch')->willReturn($fetchBuilder);
        $this->repositoryService->expects($this->once())->method('getRepository')->with($repository)->willReturn($gitRepository);
        $gitRepository->expects($this->once())->method('execute')->with($fetchBuilder, true)->willReturn('output');
        $this->fetchParser->expects($this->once())->method('parse')->with('output')->willReturn([$change]);

        static::assertSame([$change], $this->fetchService->fetch($repository));
    }
}
