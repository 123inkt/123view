<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Revision;

use Carbon\Carbon;
use DateTime;
use DR\Review\Entity\Git\Author;
use DR\Review\Entity\Git\Commit;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Service\Revision\RevisionFactory;
use DR\Review\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\Review\Service\Revision\RevisionFactory
 */
class RevisionFactoryTest extends AbstractTestCase
{
    /**
     * @covers ::createFromCommit
     */
    public function testCreateFromCommit(): void
    {
        $date       = new DateTime();
        $repository = new Repository();
        $author     = new Author('Sherlock', 'holmes@example.com');
        $commit     = new Commit($repository, 'parent', 'commit', $author, Carbon::now(), "subject", "message", 'first-branch', []);

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
        static::assertSame('first-branch', $revision->getFirstBranch());
        static::assertSame($date->getTimestamp(), $revision->getCreateTimestamp());
    }

    /**
     * @covers ::createFromCommit
     */
    public function testCreateFromCommitShouldIgnoreRefIsHash(): void
    {
        $author = new Author('Sherlock', 'holmes@example.com');
        $commit = new Commit(new Repository(), 'parent', 'commit', $author, Carbon::now(), "subject", "message", 'commit', []);

        $factory   = new RevisionFactory();
        $revisions = $factory->createFromCommit($commit);

        static::assertCount(1, $revisions);
        static::assertNull($revisions[0]->getFirstBranch());
    }
}
