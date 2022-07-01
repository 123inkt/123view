<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Entity\Git;

use DateTime;
use DR\GitCommitNotification\Entity\Git\Author;
use DR\GitCommitNotification\Entity\Git\Commit;
use DR\GitCommitNotification\Entity\Git\Diff\DiffFile;
use DR\GitCommitNotification\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Entity\Git\Commit
 */
class CommitTest extends AbstractTestCase
{
    private Commit   $commit;
    private DateTime $date;

    protected function setUp(): void
    {
        parent::setUp();
        $author     = new Author('name', 'email');
        $this->date = new DateTime();
        $refs       = 'refs/remotes/origin/foobar';
        $files      = [new DiffFile()];
        $subject    = "line1\nline2";
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
        static::assertSame("line1\nline2", $this->commit->subject);
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
}
