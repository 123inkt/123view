<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Service\Filter;

use Doctrine\Common\Collections\ArrayCollection;
use DR\GitCommitNotification\Doctrine\Type\FilterType;
use DR\GitCommitNotification\Entity\Config\Filter;
use DR\GitCommitNotification\Entity\Git\Author;
use DR\GitCommitNotification\Entity\Git\Diff\DiffFile;
use DR\GitCommitNotification\Service\Filter\CommitFilter;
use DR\GitCommitNotification\Service\Filter\DefinitionFileMatcher;
use DR\GitCommitNotification\Service\Filter\DefinitionSubjectMatcher;
use DR\GitCommitNotification\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Service\Filter\CommitFilter
 * @covers ::__construct
 */
class CommitFilterTest extends AbstractTestCase
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
        $filter = new Filter();
        $filter->setType(FilterType::AUTHOR);
        $filter->setPattern('sherlock@example.com');
        $collection = new ArrayCollection([$filter]);

        $commitA = $this->createCommit(new Author('Sherlock Holmes', 'sherlock@example.com'), [new DiffFile()]);
        $commitB = $this->createCommit(new Author('John Watson', 'watson@example.com'), [new DiffFile()]);

        static::assertSame([1 => $commitB], $this->filter->exclude([$commitA, $commitB], $collection));
        static::assertSame([0 => $commitA], $this->filter->include([$commitA, $commitB], $collection));
    }

    /**
     * @covers ::include
     * @covers ::exclude
     * @covers ::fileFilter
     */
    public function testIncludeAndExcludeBySubject(): void
    {
        $filter = new Filter();
        $filter->setType(FilterType::SUBJECT);
        $filter->setPattern('/^Foo/');
        $collection = new ArrayCollection([$filter]);

        $commitA          = $this->createCommit(null, [new DiffFile()]);
        $commitA->subject = 'Foobar';
        $commitB          = $this->createCommit(null, [new DiffFile()]);
        $commitB->subject = 'Unknown';

        static::assertSame([1 => $commitB], $this->filter->exclude([$commitA, $commitB], $collection));
        static::assertSame([0 => $commitA], $this->filter->include([$commitA, $commitB], $collection));
    }

    /**
     * @covers ::include
     * @covers ::exclude
     * @covers ::fileFilter
     */
    public function testExcludeByFile(): void
    {
        $filter = new Filter();
        $filter->setType(FilterType::FILE);
        $filter->setPattern('#/path-(a|c)/.*\\.txt$#');
        $collection = new ArrayCollection([$filter]);

        $fileA                = new DiffFile();
        $fileA->filePathAfter = '/path-a/a.txt';
        $fileB                = new DiffFile();
        $fileB->filePathAfter = '/path-b/b.txt';
        $fileC                = new DiffFile();
        $fileC->filePathAfter = '/path-c/c.txt';

        $commitA = $this->createCommit(new Author('Sherlock Holmes', 'sherlock@example.com'), [$fileA, $fileB]);
        $commitB = $this->createCommit(new Author('John Watson', 'watson@example.com'), [$fileC]);

        // expect only commitA with fileB
        $result = $this->filter->exclude([$commitA, $commitB], $collection);
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
        $filter = new Filter();
        $filter->setType(FilterType::FILE);
        $filter->setPattern('#/path-(a|c)/.*\\.txt$#');
        $collection = new ArrayCollection([$filter]);

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
        $result = $this->filter->include([$commitA, $commitB, $commitC], $collection);
        static::assertCount(2, $result);
        static::assertCount(1, $result[0]->files);
        static::assertSame($fileA, reset($result[0]->files));
        static::assertSame($commitB, $result[1]);
    }
}
