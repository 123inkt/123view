<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Service\Revision;

use DateTime;
use DR\GitCommitNotification\Entity\Git\Author;
use DR\GitCommitNotification\Entity\Git\Commit;
use DR\GitCommitNotification\Entity\Repository\Repository;
use DR\GitCommitNotification\Service\Revision\RevisionFactory;
use DR\GitCommitNotification\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Service\Revision\RevisionFactory
 */
class RevisionFactoryTest extends AbstractTestCase
{
    /**
     * @covers ::createFromCommits
     */
    public function testCreateFromCommits(): void
    {
        $date       = new DateTime();
        $repository = new Repository();
        $author     = new Author('Sherlock', 'holmes@example.com');
        $commit     = new Commit($repository, 'parent', 'commit', $author, $date, "subject\nmessage", null, []);

        $factory = new RevisionFactory();
        static::assertCount(1, $factory->createFromCommits([$commit]));
    }

    /**
     * @covers ::createFromCommit
     */
    public function testCreateFromCommit(): void
    {
        $date       = new DateTime();
        $repository = new Repository();
        $author     = new Author('Sherlock', 'holmes@example.com');
        $commit     = new Commit($repository, 'parent', 'commit', $author, $date, "subject\nmessage", null, []);

        $factory   = new RevisionFactory();
        $revisions = $factory->createFromCommit($commit);

        static::assertCount(1, $revisions);

        $revision = $revisions[0];
        static::assertSame($repository, $revision->getRepository());
        static::assertSame('commit', $revision->getCommitHash());
        static::assertSame('Sherlock', $revision->getAuthorName());
        static::assertSame('holmes@example.com', $revision->getAuthorEmail());
        static::assertSame('subject', $revision->getTitle());
        static::assertSame('message', $revision->getDescription());
        static::assertSame($date->getTimestamp(), $revision->getCreateTimestamp());
    }
}
