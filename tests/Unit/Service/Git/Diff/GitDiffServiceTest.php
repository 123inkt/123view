<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Service\Git\Diff;

use DR\GitCommitNotification\Entity\Config\Rule;
use DR\GitCommitNotification\Entity\Git\Diff\DiffFile;
use DR\GitCommitNotification\Git\GitRepository;
use DR\GitCommitNotification\Service\Git\CacheableGitRepositoryService;
use DR\GitCommitNotification\Service\Git\Diff\GitDiffCommandBuilder;
use DR\GitCommitNotification\Service\Git\Diff\GitDiffCommandFactory;
use DR\GitCommitNotification\Service\Git\Diff\GitDiffService;
use DR\GitCommitNotification\Service\Parser\DiffParser;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Service\Git\Diff\GitDiffService
 * @covers ::__construct
 */
class GitDiffServiceTest extends AbstractTestCase
{
    /** @var CacheableGitRepositoryService&MockObject */
    private CacheableGitRepositoryService $repositoryService;
    /** @var GitDiffCommandFactory&MockObject */
    private GitDiffCommandFactory $commandFactory;
    /** @var DiffParser&MockObject */
    private DiffParser     $parser;
    private GitDiffService $diffService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repositoryService = $this->createMock(CacheableGitRepositoryService::class);
        $this->commandFactory    = $this->createMock(GitDiffCommandFactory::class);
        $this->parser            = $this->createMock(DiffParser::class);
        $this->diffService       = new GitDiffService($this->log, $this->repositoryService, $this->commandFactory, $this->parser);
    }

    /**
     * @covers ::getBundledDiff
     * @throws Exception
     */
    public function testGetBundledDiff(): void
    {
        $repositoryConfig = $this->createRepository('foobar', 'http://foobar.com');
        $rule             = new Rule();
        $repository       = $this->createMock(GitRepository::class);
        $commandBuilder   = new GitDiffCommandBuilder('git');

        $files                = [new DiffFile()];
        $commit               = $this->createCommit();
        $commit->repository   = $repositoryConfig;
        $commit->parentHash   = 'parentHash';
        $commit->commitHashes = ['hash1', 'hash2', 'hash3'];

        // setup mocks
        $this->repositoryService->expects(static::once())->method('getRepository')->with('http://foobar.com')->willReturn($repository);
        $this->commandFactory->expects(static::once())->method('diffHashes')->with($rule, 'parentHash', 'hash3')->willReturn($commandBuilder);
        $repository->expects(static::once())->method('execute')->with($commandBuilder)->willReturn('foobar');
        $this->parser->expects(static::once())->method('parse')->with('foobar')->willReturn($files);

        $commit = $this->diffService->getBundledDiff($rule, $commit);
        static::assertSame($files, $commit->files);
    }

    /**
     * @covers ::getBundledDiff
     * @throws Exception
     */
    public function testGetBundledDiffShouldSkipOnSingleHash(): void
    {
        $rule                 = new Rule();
        $commit               = $this->createCommit();
        $commit->commitHashes = ['hash1'];

        // setup mocks
        $this->repositoryService->expects(static::never())->method('getRepository');

        $this->diffService->getBundledDiff($rule, $commit);
    }
}
