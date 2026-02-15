<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Git\Diff;

use PHPUnit\Framework\MockObject\Stub;
use DR\Review\Doctrine\Type\DiffAlgorithmType;
use DR\Review\Entity\Git\Diff\DiffComparePolicy;
use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Entity\Notification\Rule;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Exception\RepositoryException;
use DR\Review\Git\GitRepository;
use DR\Review\Service\Git\CacheableGitRepositoryService;
use DR\Review\Service\Git\Diff\GitDiffCommandBuilder;
use DR\Review\Service\Git\Diff\GitDiffCommandFactory;
use DR\Review\Service\Git\Diff\GitDiffService;
use DR\Review\Service\Git\GitCommandBuilderFactory;
use DR\Review\Service\Git\Review\FileDiffOptions;
use DR\Review\Service\Git\Show\GitShowCommandBuilder;
use DR\Review\Service\Parser\DiffNumStatParser;
use DR\Review\Service\Parser\PrunableDiffParser;
use DR\Review\Tests\AbstractTestCase;
use Exception;
use League\Uri\Uri;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(GitDiffService::class)]
class GitDiffServiceTest extends AbstractTestCase
{
    private CacheableGitRepositoryService&MockObject $repositoryService;
    private GitCommandBuilderFactory&MockObject      $commandBuilderFactory;
    private GitDiffCommandFactory&MockObject         $commandFactory;
    private PrunableDiffParser&MockObject            $parser;
    private DiffNumStatParser&Stub             $numStatParser;
    private GitDiffService                           $diffService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repositoryService     = $this->createMock(CacheableGitRepositoryService::class);
        $this->commandBuilderFactory = $this->createMock(GitCommandBuilderFactory::class);
        $this->commandFactory        = $this->createMock(GitDiffCommandFactory::class);
        $this->parser                = $this->createMock(PrunableDiffParser::class);
        $this->numStatParser         = static::createStub(DiffNumStatParser::class);
        $this->diffService           = new GitDiffService(
            $this->repositoryService,
            $this->commandBuilderFactory,
            $this->commandFactory,
            $this->parser,
            $this->numStatParser
        );
    }

    /**
     * @throws Exception
     */
    public function testGetBundledDiff(): void
    {
        $repositoryConfig = $this->createRepository('foobar', 'https://foobar.com');
        $rule             = new Rule();
        $repository       = $this->createMock(GitRepository::class);
        $commandBuilder   = new GitDiffCommandBuilder('git');

        $files                = [new DiffFile()];
        $commit               = $this->createCommit();
        $commit->repository   = $repositoryConfig;
        $commit->parentHash   = 'parentHash';
        $commit->commitHashes = ['hash1', 'hash2', 'hash3'];

        // setup mocks
        $this->repositoryService->expects($this->once())->method('getRepository')->with($repositoryConfig)->willReturn($repository);
        $this->commandFactory->expects($this->once())->method('diffHashes')->with($rule, 'parentHash', 'hash3')->willReturn($commandBuilder);
        $repository->expects($this->once())->method('execute')->with($commandBuilder)->willReturn('foobar');
        $this->parser->expects($this->once())->method('parse')->with('foobar', null)->willReturn($files);
        $this->commandBuilderFactory->expects($this->never())->method('createShow');

        $commit = $this->diffService->getBundledDiff($rule, $commit);
        static::assertSame($files, $commit->files);
    }

    /**
     * @throws Exception
     */
    public function testGetBundledDiffShouldSkipOnSingleHash(): void
    {
        $rule                 = new Rule();
        $commit               = $this->createCommit();
        $commit->commitHashes = ['hash1'];

        // setup mocks
        $this->repositoryService->expects(static::never())->method('getRepository');
        $this->commandBuilderFactory->expects($this->never())->method('createShow');
        $this->commandFactory->expects($this->never())->method('diffHashes');
        $this->parser->expects($this->never())->method('parse');

        $this->diffService->getBundledDiff($rule, $commit);
    }

    /**
     * @throws Exception
     */
    public function testGetDiffFromRevision(): void
    {
        $repository = new Repository();
        $repository->setUrl(Uri::new('https://foobar.com'));
        $revision = new Revision();
        $revision->setRepository($repository);
        $revision->setCommitHash('commit-hash');

        $builder = $this->createMock(GitShowCommandBuilder::class);
        $builder->expects($this->once())->method('startPoint')->with('commit-hash')->willReturnSelf();
        $builder->expects($this->once())->method('ignoreCrAtEol')->willReturnSelf();
        $builder->expects($this->once())->method('ignoreSpaceAtEol')->willReturnSelf();
        $builder->expects($this->once())->method('ignoreSpaceChange')->willReturnSelf();
        $builder->expects($this->once())->method('unified')->with(5)->willReturnSelf();
        $this->commandBuilderFactory->expects($this->once())->method('createShow')->willReturn($builder);

        $gitRepository = $this->createMock(GitRepository::class);
        $gitRepository->expects($this->once())->method('execute')->with($builder)->willReturn('foobar');
        $this->repositoryService->expects($this->once())->method('getRepository')->with($repository)->willReturn($gitRepository);
        $this->parser->expects($this->once())->method('parse')->with('foobar', DiffComparePolicy::TRIM);
        $this->commandFactory->expects($this->never())->method('diffHashes');

        $this->diffService->getDiffFromRevision($revision, new FileDiffOptions(5, DiffComparePolicy::TRIM));
    }

    /**
     * @throws Exception
     */
    public function testGetDiffFromRevisionWithIgnoreSpace(): void
    {
        $repository = new Repository();
        $repository->setUrl(Uri::new('https://foobar.com'));
        $revision = new Revision();
        $revision->setRepository($repository);
        $revision->setCommitHash('commit-hash');

        $builder = $this->createMock(GitShowCommandBuilder::class);
        $builder->expects($this->once())->method('startPoint')->with('commit-hash')->willReturnSelf();
        $builder->expects($this->once())->method('ignoreCrAtEol')->willReturnSelf();
        $builder->expects($this->once())->method('ignoreSpaceAtEol')->willReturnSelf();
        $builder->expects($this->once())->method('ignoreAllSpace')->willReturnSelf();
        $builder->expects($this->once())->method('unified')->with(5)->willReturnSelf();
        $this->commandBuilderFactory->expects($this->once())->method('createShow')->willReturn($builder);

        $gitRepository = $this->createMock(GitRepository::class);
        $gitRepository->expects($this->once())->method('execute')->with($builder)->willReturn('foobar');
        $this->repositoryService->expects($this->once())->method('getRepository')->with($repository)->willReturn($gitRepository);
        $this->parser->expects($this->once())->method('parse')->with('foobar', DiffComparePolicy::IGNORE);
        $this->commandFactory->expects($this->never())->method('diffHashes');

        $this->diffService->getDiffFromRevision($revision, new FileDiffOptions(5, DiffComparePolicy::IGNORE));
    }

    /**
     * @throws Exception
     */
    public function testGetBundledDiffFromRevisions(): void
    {
        $repository = new Repository();
        $repository->setUrl(Uri::new('https://foobar.com'));

        $builder = $this->createMock(GitDiffCommandBuilder::class);
        $builder->expects($this->once())->method('hash')->with('HEAD')->willReturnSelf();
        $builder->expects($this->once())->method('unified')->with(15)->willReturnSelf();
        $builder->expects($this->once())->method('diffAlgorithm')->with(DiffAlgorithmType::MYERS)->willReturnSelf();
        $builder->expects($this->once())->method('ignoreCrAtEol')->willReturnSelf();
        $builder->expects($this->once())->method('ignoreSpaceAtEol')->willReturnSelf();
        $builder->expects($this->once())->method('ignoreSpaceChange')->willReturnSelf();
        $this->commandBuilderFactory->expects($this->once())->method('createDiff')->willReturn($builder);

        $gitRepository = $this->createMock(GitRepository::class);
        $gitRepository->expects($this->once())->method('execute')->with($builder)->willReturn('foobar');
        $this->repositoryService->expects($this->once())->method('getRepository')->with($repository)->willReturn($gitRepository);
        $this->parser->expects($this->once())->method('parse')->with('foobar', DiffComparePolicy::TRIM);
        $this->commandFactory->expects($this->never())->method('diffHashes');

        $this->diffService->getBundledDiffFromRevisions($repository, new FileDiffOptions(15, DiffComparePolicy::TRIM));
    }

    /**
     * @throws Exception
     */
    public function testGetBundledDiffFromRevisionsIgnoreAllSpaceChange(): void
    {
        $repository = new Repository();
        $repository->setUrl(Uri::new('https://foobar.com'));

        $builder = $this->createMock(GitDiffCommandBuilder::class);
        $builder->expects($this->once())->method('hash')->with('HEAD')->willReturnSelf();
        $builder->expects($this->once())->method('unified')->with(15)->willReturnSelf();
        $builder->expects($this->once())->method('diffAlgorithm')->with(DiffAlgorithmType::MYERS)->willReturnSelf();
        $builder->expects($this->once())->method('ignoreCrAtEol')->willReturnSelf();
        $builder->expects($this->once())->method('ignoreSpaceAtEol')->willReturnSelf();
        $builder->expects($this->once())->method('ignoreAllSpace')->willReturnSelf();
        $this->commandBuilderFactory->expects($this->once())->method('createDiff')->willReturn($builder);

        $gitRepository = $this->createMock(GitRepository::class);
        $gitRepository->expects($this->once())->method('execute')->with($builder)->willReturn('foobar');
        $this->repositoryService->expects($this->once())->method('getRepository')->with($repository)->willReturn($gitRepository);
        $this->parser->expects($this->once())->method('parse')->with('foobar', DiffComparePolicy::IGNORE);
        $this->commandFactory->expects($this->never())->method('diffHashes');

        $this->diffService->getBundledDiffFromRevisions($repository, new FileDiffOptions(15, DiffComparePolicy::IGNORE));
    }

    /**
     * @throws Exception
     */
    public function testGetBundledDiffFromBranch(): void
    {
        $repository = new Repository();
        $repository->setUrl(Uri::new('https://foobar.com'));

        $builder = $this->createMock(GitDiffCommandBuilder::class);
        $builder->expects($this->once())->method('hash')->with('target...source')->willReturnSelf();
        $builder->expects($this->once())->method('unified')->with(15)->willReturnSelf();
        $builder->expects($this->once())->method('diffAlgorithm')->with(DiffAlgorithmType::MYERS)->willReturnSelf();
        $builder->expects($this->once())->method('ignoreCrAtEol')->willReturnSelf();
        $builder->expects($this->once())->method('ignoreSpaceAtEol')->willReturnSelf();
        $builder->expects($this->once())->method('ignoreSpaceChange')->willReturnSelf();
        $this->commandBuilderFactory->expects($this->once())->method('createDiff')->willReturn($builder);

        $gitRepository = $this->createMock(GitRepository::class);
        $gitRepository->expects($this->once())->method('execute')->with($builder)->willReturn('foobar');
        $this->repositoryService->expects($this->once())->method('getRepository')->with($repository)->willReturn($gitRepository);
        $this->parser->expects($this->once())->method('parse')->with('foobar', DiffComparePolicy::TRIM);
        $this->commandFactory->expects($this->never())->method('diffHashes');

        $this->diffService->getBundledDiffFromBranch($repository, 'source', 'target', new FileDiffOptions(15, DiffComparePolicy::TRIM));
    }

    /**
     * @throws Exception
     */
    public function testGetBundledDiffFromBranchIgnoreAllSpaceChange(): void
    {
        $repository = new Repository();
        $repository->setUrl(Uri::new('https://foobar.com'));

        $builder = $this->createMock(GitDiffCommandBuilder::class);
        $builder->expects($this->once())->method('hash')->with('target...source')->willReturnSelf();
        $builder->expects($this->once())->method('unified')->with(15)->willReturnSelf();
        $builder->expects($this->once())->method('diffAlgorithm')->with(DiffAlgorithmType::MYERS)->willReturnSelf();
        $builder->expects($this->once())->method('ignoreCrAtEol')->willReturnSelf();
        $builder->expects($this->once())->method('ignoreSpaceAtEol')->willReturnSelf();
        $builder->expects($this->once())->method('ignoreAllSpace')->willReturnSelf();
        $this->commandBuilderFactory->expects($this->once())->method('createDiff')->willReturn($builder);

        $gitRepository = $this->createMock(GitRepository::class);
        $gitRepository->expects($this->once())->method('execute')->with($builder)->willReturn('foobar');
        $this->repositoryService->expects($this->once())->method('getRepository')->with($repository)->willReturn($gitRepository);
        $this->parser->expects($this->once())->method('parse')->with('foobar', DiffComparePolicy::IGNORE);
        $this->commandFactory->expects($this->never())->method('diffHashes');

        $this->diffService->getBundledDiffFromBranch($repository, 'source', 'target', new FileDiffOptions(15, DiffComparePolicy::IGNORE));
    }

    /**
     * @throws RepositoryException
     */
    public function testGetRevisionFiles(): void
    {
        $repository = new Repository();
        $revision   = (new Revision())->setCommitHash('target');
        $revision->setRepository($repository);

        $builder = $this->createMock(GitDiffCommandBuilder::class);
        $builder->expects($this->once())->method('hash')->with('target^!')->willReturnSelf();
        $builder->expects($this->once())->method('numStat')->willReturnSelf();

        $gitRepository = $this->createMock(GitRepository::class);
        $gitRepository->expects($this->once())->method('execute')->with($builder)->willReturn('foobar');
        $this->repositoryService->expects($this->once())->method('getRepository')->with($repository)->willReturn($gitRepository);
        $this->commandBuilderFactory->expects($this->once())->method('createDiff')->willReturn($builder);
        $this->commandFactory->expects($this->never())->method('diffHashes');
        $this->parser->expects($this->never())->method('parse');

        $this->diffService->getRevisionFiles($revision);
    }
}
