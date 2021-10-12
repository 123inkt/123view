<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Service\Filter;

use DR\GitCommitNotification\Entity\Config\Definition;
use DR\GitCommitNotification\Entity\Git\Author;
use DR\GitCommitNotification\Entity\Git\Diff\DiffFile;
use DR\GitCommitNotification\Service\Filter\CommitFilter;
use DR\GitCommitNotification\Service\Filter\DefinitionFileMatcher;
use DR\GitCommitNotification\Service\Filter\DefinitionSubjectMatcher;
use DR\GitCommitNotification\Tests\AbstractTest;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Service\Filter\CommitFilter
 * @covers ::__construct
 */
class CommitFilterTest extends AbstractTest
{
    private CommitFilter $filter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->filter = new CommitFilter($this->log, new DefinitionFileMatcher(), new DefinitionSubjectMatcher());
    }

    /**
     * @covers ::include
     * @covers ::exclude
     * @covers ::fileFilter
     */
    public function testIncludeAndExcludeByAuthor(): void
    {
        $definition = new Definition();
        $definition->addAuthor('sherlock@example.com');

        $commitA = $this->createCommit(new Author('Sherlock Holmes', 'sherlock@example.com'), [new DiffFile()]);
        $commitB = $this->createCommit(new Author('John Watson', 'watson@example.com'), [new DiffFile()]);

        static::assertSame([1 => $commitB], $this->filter->exclude([$commitA, $commitB], $definition));
        static::assertSame([0 => $commitA], $this->filter->include([$commitA, $commitB], $definition));
    }

    /**
     * @covers ::include
     * @covers ::exclude
     * @covers ::fileFilter
     */
    public function testIncludeAndExcludeBySubject(): void
    {
        $definition = new Definition();
        $definition->addSubject('/^Foo/');

        $commitA = $this->createCommit(null, [new DiffFile()]);
        $commitA->subject = 'Foobar';
        $commitB = $this->createCommit(null, [new DiffFile()]);
        $commitB->subject = 'Unknown';

        static::assertSame([1 => $commitB], $this->filter->exclude([$commitA, $commitB], $definition));
        static::assertSame([0 => $commitA], $this->filter->include([$commitA, $commitB], $definition));
    }

    /**
     * @covers ::include
     * @covers ::exclude
     * @covers ::fileFilter
     */
    public function testExcludeByFile(): void
    {
        $definition = new Definition();
        $definition->addFile('#/path-(a|c)/.*\\.txt$#');

        $fileA                = new DiffFile();
        $fileA->filePathAfter = '/path-a/a.txt';
        $fileB                = new DiffFile();
        $fileB->filePathAfter = '/path-b/b.txt';
        $fileC                = new DiffFile();
        $fileC->filePathAfter = '/path-c/c.txt';

        $commitA = $this->createCommit(new Author('Sherlock Holmes', 'sherlock@example.com'), [$fileA, $fileB]);
        $commitB = $this->createCommit(new Author('John Watson', 'watson@example.com'), [$fileC]);

        // expect only commitA with fileB
        $result = $this->filter->exclude([$commitA, $commitB], $definition);
        static::assertCount(1, $result);
        static::assertCount(1, $result[0]->files);
        static::assertSame($fileB, reset($result[0]->files));
    }

    /**
     * @covers ::include
     * @covers ::exclude
     * @covers ::fileFilter
     */
    public function testIncludeByFile(): void
    {
        $definition = new Definition();
        $definition->addFile('#/path-(a|c)/.*\\.txt$#');

        $fileA                = new DiffFile();
        $fileA->filePathAfter = '/path-a/a.txt';
        $fileB                = new DiffFile();
        $fileB->filePathAfter = '/path-b/b.txt';
        $fileC                = new DiffFile();
        $fileC->filePathAfter = '/path-c/c.txt';

        $commitA = $this->createCommit(new Author('Sherlock Holmes', 'sherlock@example.com'), [$fileA, $fileB]);
        $commitB = $this->createCommit(new Author('John Watson', 'watson@example.com'), [$fileC]);
        $commitC = $this->createCommit(new Author('Mary Watson', 'mary@example.com'), [$fileB]);

        // expect fileB from commitA be removed, and commitC
        $result = $this->filter->include([$commitA, $commitB, $commitC], $definition);
        static::assertCount(2, $result);
        static::assertCount(1, $result[0]->files);
        static::assertSame($fileA, reset($result[0]->files));
        static::assertSame($commitB, $result[1]);
    }
}
