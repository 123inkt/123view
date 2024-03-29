<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service;

use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Git\FormatPattern;
use DR\Review\Service\CommitHydrator;
use DR\Review\Tests\AbstractTestCase;
use Exception;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CommitHydrator::class)]
class CommitHydratorTest extends AbstractTestCase
{
    private const DATA       = [
        FormatPattern::AUTHOR_NAME         => 'Sherlock Holmes',
        FormatPattern::AUTHOR_EMAIL        => 'sherlock@example.com',
        FormatPattern::AUTHOR_DATE_ISO8601 => '2019-09-07T15:50-04:00',
        FormatPattern::REF_NAMES           => '',
        FormatPattern::REF_NAME_SOURCE     => 'origin/foobar',
        FormatPattern::COMMIT_HASH         => 'commit-hash',
        FormatPattern::PARENT_HASH         => 'parent-hash',
        FormatPattern::SUBJECT             => 'subject',
        FormatPattern::BODY                => 'body'
    ];
    private const REPOSITORY = "https://repository.com/";

    private CommitHydrator $hydrator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->hydrator = new CommitHydrator();
    }

    /**
     * @throws Exception
     */
    public function testHydrate(): void
    {
        $files  = [new DiffFile()];
        $commit = $this->hydrator->hydrate($this->createRepository('repository', self::REPOSITORY), self::DATA, $files);

        static::assertSame(self::REPOSITORY, (string)$commit->repository->getUrl());
        static::assertSame([self::DATA[FormatPattern::COMMIT_HASH]], $commit->commitHashes);
        static::assertSame(self::DATA[FormatPattern::AUTHOR_NAME], $commit->author->name);
        static::assertSame(self::DATA[FormatPattern::AUTHOR_EMAIL], $commit->author->email);
        static::assertSame('origin/foobar', $commit->refs);
        static::assertSame('2019-09-07', $commit->date->format('Y-m-d'));
        static::assertSame(self::DATA[FormatPattern::SUBJECT], $commit->subject);
        static::assertSame($files, $commit->files);
    }
}
