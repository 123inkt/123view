<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Service\Git\Commit;

use DR\GitCommitNotification\Entity\Config\Repository;
use DR\GitCommitNotification\Entity\Git\Author;
use DR\GitCommitNotification\Service\Git\Commit\CommitBundler;
use DR\GitCommitNotification\Service\Git\Commit\CommitCombiner;
use DR\GitCommitNotification\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Service\Git\Commit\CommitBundler
 * @covers ::__construct
 */
class CommitBundlerTest extends AbstractTestCase
{
    private CommitBundler $bundler;

    protected function setUp(): void
    {
        parent::setUp();
        $combiner = $this->createMock(CommitCombiner::class);
        $combiner->method('combine')->willReturnCallback(static fn(array $commits) => reset($commits));
        $this->bundler = new CommitBundler($combiner);
    }

    /**
     * @covers ::equals
     * @covers ::getGroupedCommits
     * @covers ::bundle
     */
    public function testBundleShouldGroupCommits(): void
    {
        $commitA               = $this->createCommit();
        $commitA->commitHashes = ['parent-commit'];
        $commitB               = $this->createCommit();
        $commitB->parentHash   = 'parent-commit';
        $commitB->commitHashes = ['foobar'];

        $commitA->repository = $commitB->repository = new Repository();

        $result = $this->bundler->bundle([$commitA, $commitB]);
        static::assertSame([$commitA], $result);
    }

    /**
     * @covers ::equals
     * @covers ::getGroupedCommits
     * @covers ::bundle
     */
    public function testBundleShouldGroupMultipleCommits(): void
    {
        $commitA               = $this->createCommit();
        $commitA->commitHashes = ['parent-commit-a'];
        $commitB               = $this->createCommit();
        $commitB->parentHash   = 'parent-commit-a';
        $commitB->commitHashes = ['parent-commit-b'];
        $commitC               = $this->createCommit();
        $commitC->parentHash   = 'parent-commit-b';
        $commitC->commitHashes = ['foobar'];

        $commitA->repository = $commitB->repository = $commitC->repository = new Repository();

        $result = $this->bundler->bundle([$commitA, $commitB]);
        static::assertSame([$commitA], $result);
    }

    /**
     * @covers ::equals
     * @covers ::getGroupedCommits
     * @covers ::bundle
     */
    public function testBundleShouldNotGroupCommitsIfEmailDiffers(): void
    {
        $commitA             = $this->createCommit(new Author('jane', 'jane@example.com'));
        $commitB             = $this->createCommit(new Author('john', 'john@example.com'));
        $commitA->repository = $commitB->repository = new Repository();

        $result = $this->bundler->bundle([$commitA, $commitB]);
        static::assertSame([$commitA, $commitB], $result);
    }

    /**
     * @covers ::equals
     * @covers ::getGroupedCommits
     * @covers ::bundle
     */
    public function testBundleShouldNotGroupCommitsIfSubjectDiffers(): void
    {
        $commitA             = $this->createCommit();
        $commitB             = $this->createCommit();
        $commitA->repository = $commitB->repository = new Repository();

        $commitA->subject = 'subject A';
        $commitB->subject = 'subject B';

        $result = $this->bundler->bundle([$commitA, $commitB]);
        static::assertSame([$commitA, $commitB], $result);
    }

    /**
     * @covers ::equals
     * @covers ::getGroupedCommits
     * @covers ::bundle
     */
    public function testBundleShouldNotGroupCommitsIfRemoteDiffers(): void
    {
        $commitA             = $this->createCommit();
        $commitB             = $this->createCommit();
        $commitA->repository = $commitB->repository = new Repository();

        $commitA->refs = 'refs/remotes/origin/A';
        $commitB->refs = 'refs/remotes/origin/B';

        $result = $this->bundler->bundle([$commitA, $commitB]);
        static::assertSame([$commitA, $commitB], $result);
    }

    /**
     * @covers ::equals
     * @covers ::getGroupedCommits
     * @covers ::bundle
     */
    public function testBundleShouldNotGroupCommitsIfRepositoryDiffers(): void
    {
        $commitA = $this->createCommit();
        $commitB = $this->createCommit();

        $commitA->repository = new Repository();
        $commitB->repository = new Repository();

        $result = $this->bundler->bundle([$commitA, $commitB]);
        static::assertSame([$commitA, $commitB], $result);
    }
}
