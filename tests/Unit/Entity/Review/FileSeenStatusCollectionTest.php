<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Entity\Review;

use DR\Review\Entity\Review\FileSeenStatus;
use DR\Review\Entity\Review\FileSeenStatusCollection;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(FileSeenStatusCollection::class)]
class FileSeenStatusCollectionTest extends AbstractTestCase
{
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
