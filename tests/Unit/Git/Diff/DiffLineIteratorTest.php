<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Git\Diff;

use Carbon\Carbon;
use DR\Review\Entity\Git\Author;
use DR\Review\Entity\Git\Commit;
use DR\Review\Entity\Git\Diff\DiffBlock;
use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Entity\Git\Diff\DiffLine;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Git\Diff\DiffLineIterator;
use DR\Review\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\Review\Git\Diff\DiffLineIterator
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

        $file = new DiffFile();
        $file->addBlock($block);

        $commit = new Commit(new Repository(), 'parent-hash', 'hash', new Author('name', 'email'), Carbon::now(), 'subject', 'refs', [$file]);

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
