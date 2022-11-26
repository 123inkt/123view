<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Service\Git\Diff;

use DR\GitCommitNotification\Doctrine\Type\DiffAlgorithmType;
use DR\GitCommitNotification\Entity\Git\Diff\DiffFile;
use DR\GitCommitNotification\Entity\Notification\Rule;
use DR\GitCommitNotification\Entity\Repository\Repository;
use DR\GitCommitNotification\Entity\Review\Revision;
use DR\GitCommitNotification\Git\GitRepository;
use DR\GitCommitNotification\Service\Git\CacheableGitRepositoryService;
use DR\GitCommitNotification\Service\Git\Diff\GitDiffCommandBuilder;
use DR\GitCommitNotification\Service\Git\Diff\GitDiffCommandFactory;
use DR\GitCommitNotification\Service\Git\Diff\GitDiffService;
use DR\GitCommitNotification\Service\Git\GitCommandBuilderFactory;
use DR\GitCommitNotification\Service\Git\Review\FileDiffOptions;
use DR\GitCommitNotification\Service\Git\Show\GitShowCommandBuilder;
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
    private CacheableGitRepositoryService&MockObject $repositoryService;
    private GitCommandBuilderFactory&MockObject      $commandBuilderFactory;
    private GitDiffCommandFactory&MockObject         $commandFactory;
    private DiffParser&MockObject                    $parser;
    private GitDiffService                           $diffService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repositoryService     = $this->createMock(CacheableGitRepositoryService::class);
        $this->commandBuilderFactory = $this->createMock(GitCommandBuilderFactory::class);
        $this->commandFactory        = $this->createMock(GitDiffCommandFactory::class);
        $this->parser                = $this->createMock(DiffParser::class);
        $this->diffService           = new GitDiffService(
            $this->repositoryService,
            $this->commandBuilderFactory,
            $this->commandFactory,
            $this->parser
        );
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

    /**
     * @covers ::getDiffFromRevision
     * @throws Exception
     */
    public function testGetDiffFromRevision(): void
    {
        $repository = new Repository();
        $repository->setUrl('http://foobar.com');
        $revision = new Revision();
        $revision->setRepository($repository);
        $revision->setCommitHash('commit-hash');

        $builder = $this->createMock(GitShowCommandBuilder::class);
        $builder->expects(self::once())->method('startPoint')->with('commit-hash')->willReturnSelf();
        $builder->expects(self::once())->method('unified')->with(5)->willReturnSelf();
        $this->commandBuilderFactory->expects(self::once())->method('createShow')->willReturn($builder);

        $gitRepository = $this->createMock(GitRepository::class);
        $gitRepository->expects(static::once())->method('execute')->with($builder)->willReturn('foobar');
        $this->repositoryService->expects(static::once())->method('getRepository')->with('http://foobar.com')->willReturn($gitRepository);
        $this->parser->expects(self::once())->method('parse')->with('foobar');

        $this->diffService->getDiffFromRevision($revision, new FileDiffOptions(5));
    }

    /**
     * @covers ::getBundledDiffFromRevisions
     * @throws Exception
     */
    public function testGetBundledDiffFromRevisions(): void
    {
        $repository = new Repository();
        $repository->setUrl('http://foobar.com');

        $builder = $this->createMock(GitDiffCommandBuilder::class);
        $builder->expects(self::once())->method('hash')->with('HEAD')->willReturnSelf();
        $builder->expects(self::once())->method('unified')->with(15)->willReturnSelf();
        $builder->expects(self::once())->method('diffAlgorithm')->with(DiffAlgorithmType::MYERS)->willReturnSelf();
        $builder->expects(self::once())->method('ignoreCrAtEol')->willReturnSelf();
        $builder->expects(self::once())->method('ignoreSpaceAtEol')->willReturnSelf();
        $this->commandBuilderFactory->expects(self::once())->method('createDiff')->willReturn($builder);

        $gitRepository = $this->createMock(GitRepository::class);
        $gitRepository->expects(static::once())->method('execute')->with($builder)->willReturn('foobar');
        $this->repositoryService->expects(static::once())->method('getRepository')->with('http://foobar.com')->willReturn($gitRepository);
        $this->parser->expects(self::once())->method('parse')->with('foobar');

        $this->diffService->getBundledDiffFromRevisions($repository, 15);
    }
}
