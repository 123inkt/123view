<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Git\Commit;

use DR\Review\Entity\Git\Author;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Service\Git\Commit\CommitBundler;
use DR\Review\Service\Git\Commit\CommitCombiner;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CommitBundler::class)]
class CommitBundlerTest extends AbstractTestCase
{
    private CommitBundler $bundler;

    protected function setUp(): void
    {
        parent::setUp();
        $combiner = static::createStub(CommitCombiner::class);
        $combiner->method('combine')->willReturnCallback(static fn(array $commits) => reset($commits));
        $this->bundler = new CommitBundler($combiner);
    }

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

    public function testBundleShouldNotGroupCommitsIfEmailDiffers(): void
    {
        $commitA             = $this->createCommit(new Author('jane', 'jane@example.com'));
        $commitB             = $this->createCommit(new Author('john', 'john@example.com'));
        $commitA->repository = $commitB->repository = new Repository();

        $result = $this->bundler->bundle([$commitA, $commitB]);
        static::assertSame([$commitA, $commitB], $result);
    }

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
