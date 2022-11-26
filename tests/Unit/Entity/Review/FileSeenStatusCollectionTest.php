<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Entity\Review;

use DR\GitCommitNotification\Entity\Review\FileSeenStatus;
use DR\GitCommitNotification\Entity\Review\FileSeenStatusCollection;
use DR\GitCommitNotification\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Entity\Review\FileSeenStatusCollection
 */
class FileSeenStatusCollectionTest extends AbstractTestCase
{
    /**
     * @covers ::isSeen
     */
    public function testIsSeen(): void
    {
        $statusA = new FileSeenStatus();
        $statusB = new FileSeenStatus();

        $statusA->setFilePath('/path/to/file/example.txt');
        $statusB->setFilePath('/path/to/file/example.doc');

        $collection = new FileSeenStatusCollection();
        $collection->add($statusA);
        $collection->add($statusB);

        static::assertTrue($collection->isSeen('/path/to/file/example.txt'));
        static::assertTrue($collection->isSeen('/path/to/file/example.doc'));
        static::assertFalse($collection->isSeen('/path/to/file/example.xls'));
    }
}
