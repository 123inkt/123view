<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Entity\Git;

use Carbon\Carbon;
use DateTime;
use DR\Review\Entity\Git\Author;
use DR\Review\Entity\Git\Commit;
use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\Review\Entity\Git\Commit
 */
class CommitTest extends AbstractTestCase
{
    private Commit   $commit;
    private DateTime $date;

    protected function setUp(): void
    {
        parent::setUp();
        $author     = new Author('name', 'email');
        $this->date = Carbon::now();
        $refs       = 'refs/remotes/origin/foobar';
        $files      = [new DiffFile()];
        $subject    = "line1\nline2\nline3\n\n";
        $repository = $this->createRepository('example', 'http://example.com/my/repository.git');

        $this->commit = new Commit($repository, 'parent-hash', 'commit-hash', $author, $this->date, $subject, $refs, $files);
    }

    /**
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        static::assertSame(['commit-hash'], $this->commit->commitHashes);
        static::assertSame('name', $this->commit->author->name);
        static::assertSame($this->date, $this->commit->date);
        static::assertSame("line1\nline2\nline3\n\n", $this->commit->subject);
        static::assertNotEmpty($this->commit->files);
    }

    /**
     * @covers ::getRepositoryName
     */
    public function testGetRepositoryName(): void
    {
        static::assertSame('repository', $this->commit->getRepositoryName());
    }

    /**
     * @covers ::getRemoteRef
     */
    public function testGetRemoteRef(): void
    {
        static::assertSame('foobar', $this->commit->getRemoteRef());

        $this->commit->refs = null;
        static::assertNull($this->commit->getRemoteRef());

        $this->commit->refs = 'foobar';
        static::assertNull($this->commit->getRemoteRef());
    }

    /**
     * @covers ::getSubjectLine
     */
    public function testGetSubjectLine(): void
    {
        static::assertSame('line1', $this->commit->getSubjectLine());
    }

    /**
     * @covers ::getCommitMessage
     */
    public function testGetCommitMessage(): void
    {
        static::assertSame("line2\nline3", $this->commit->getCommitMessage());
    }
}
