<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Service;

use DR\GitCommitNotification\Entity\Git\Diff\DiffFile;
use DR\GitCommitNotification\Git\FormatPattern;
use DR\GitCommitNotification\Service\CommitHydrator;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use Exception;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Service\CommitHydrator
 */
class CommitHydratorTest extends AbstractTestCase
{
    private const DATA       = [
        FormatPattern::AUTHOR_NAME         => 'Sherlock Holmes',
        FormatPattern::AUTHOR_EMAIL        => 'sherlock@example.com',
        FormatPattern::AUTHOR_DATE_ISO8601 => '2019-09-07T15:50-04:00',
        FormatPattern::REF_NAMES           => '/refs/remote/origin/foobar',
        FormatPattern::COMMIT_HASH         => 'commit-hash',
        FormatPattern::PARENT_HASH         => 'parent-hash',
        FormatPattern::SUBJECT             => 'subject',
        FormatPattern::BODY                => 'body'
    ];
    private const REPOSITORY = "http://repository.com/";

    private CommitHydrator $hydrator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->hydrator = new CommitHydrator();
    }

    /**
     * @covers ::hydrate
     * @throws Exception
     */
    public function testHydrate(): void
    {
        $files  = [new DiffFile()];
        $commit = $this->hydrator->hydrate($this->createRepository('repository', self::REPOSITORY), self::DATA, $files);

        static::assertSame(self::REPOSITORY, $commit->repository->getUrl());
        static::assertSame([self::DATA[FormatPattern::COMMIT_HASH]], $commit->commitHashes);
        static::assertSame(self::DATA[FormatPattern::AUTHOR_NAME], $commit->author->name);
        static::assertSame(self::DATA[FormatPattern::AUTHOR_EMAIL], $commit->author->email);
        static::assertSame('2019-09-07', $commit->date->format('Y-m-d'));
        static::assertSame(self::DATA[FormatPattern::SUBJECT], $commit->subject);
        static::assertSame($files, $commit->files);
    }
}
