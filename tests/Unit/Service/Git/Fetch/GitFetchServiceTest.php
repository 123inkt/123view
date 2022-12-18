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
use DR\Review\Service\Git\Log\GitLogService;
use DR\Review\Service\Parser\Fetch\GitFetchParser;
use DR\Review\Tests\AbstractTestCase;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @coversDefaultClass \DR\Review\Service\Git\Fetch\GitFetchService
 * @covers ::__construct
 */
class GitFetchServiceTest extends AbstractTestCase
{
    private GitCommandBuilderFactory&MockObject $commandBuilderFactory;
    private GitFetchParser&MockObject           $fetchParser;
    private GitLogService&MockObject            $logService;
    private GitRepositoryService&MockObject     $repositoryService;
    private GitFetchService                     $fetchService;

    public function setUp(): void
    {
        parent::setUp();
        $this->commandBuilderFactory = $this->createMock(GitCommandBuilderFactory::class);
        $this->fetchParser           = $this->createMock(GitFetchParser::class);
        $this->logService            = $this->createMock(GitLogService::class);
        $this->repositoryService     = $this->createMock(GitRepositoryService::class);
        $this->fetchService          = new GitFetchService(
            $this->commandBuilderFactory,
            $this->fetchParser,
            $this->logService,
            $this->repositoryService
        );
    }

    /**
     * @covers ::fetch
     * @throws Exception
     */
    public function testFetch(): void
    {
        $repository = new Repository();
        $repository->setId(123);
        $repository->setUrl('https://www.example.com');
        $change = new BranchUpdate('from', 'to', 'oldBranch', 'newBranch');

        $gitRepository = $this->createMock(GitRepository::class);

        $fetchBuilder = $this->createMock(GitFetchCommandBuilder::class);
        $fetchBuilder->expects(self::once())->method('all')->willReturnSelf();
        $fetchBuilder->expects(self::once())->method('verbose')->willReturnSelf();

        $this->commandBuilderFactory->expects(self::once())->method('createFetch')->willReturn($fetchBuilder);
        $this->repositoryService->expects(self::once())->method('getRepository')->with('https://www.example.com')->willReturn($gitRepository);
        $gitRepository->expects(self::once())->method('execute')->with($fetchBuilder, true)->willReturn('output');
        $this->fetchParser->expects(self::once())->method('parse')->with('output')->willReturn([$change]);

        static::assertSame([$change], $this->fetchService->fetch($repository));
    }
}
