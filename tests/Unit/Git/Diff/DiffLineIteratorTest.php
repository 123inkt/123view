<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Git\Diff;

use DateTime;
use DR\GitCommitNotification\Entity\Config\Repository;
use DR\GitCommitNotification\Entity\Git\Author;
use DR\GitCommitNotification\Entity\Git\Commit;
use DR\GitCommitNotification\Entity\Git\Diff\DiffBlock;
use DR\GitCommitNotification\Entity\Git\Diff\DiffFile;
use DR\GitCommitNotification\Entity\Git\Diff\DiffLine;
use DR\GitCommitNotification\Git\Diff\DiffLineIterator;
use DR\GitCommitNotification\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Git\Diff\DiffLineIterator
 * @covers ::__construct
 */
class DiffLineIteratorTest extends AbstractTestCase
{
    /**
     * @covers ::getIterator
     */
    public function testGetIterator(): void
    {
        // setup data
        $lineA = new DiffLine(DiffLine::STATE_REMOVED, []);
        $lineB = new DiffLine(DiffLine::STATE_ADDED, []);

        $block        = new DiffBlock();
        $block->lines = [$lineA, $lineB];

        $file         = new DiffFile();
        $file->blocks = [$block];

        $commit = new Commit(new Repository(), 'parent-hash', 'hash', new Author('name', 'email'), new DateTime(), 'subject', 'refs', [$file]);

        // execute iterator
        $result = iterator_to_array(new DiffLineIterator([$commit]));
        static::assertSame([$lineA, $lineB], $result);
    }

    /**
     * @covers ::getIterator
     */
    public function testGetIteratorOnEmptyArray(): void
    {
        $result = iterator_to_array(new DiffLineIterator([]));
        static::assertSame([], $result);
    }
}
