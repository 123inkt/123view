<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Entity\Review;

use DR\Review\Entity\Review\FolderCollapseStatus;
use DR\Review\Entity\Review\FolderCollapseStatusCollection;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(FolderCollapseStatusCollection::class)]
class FolderCollapseStatusCollectionTest extends AbstractTestCase
{
    public function testIsCollapsed(): void
    {
        $status = new FolderCollapseStatus();
        $status->setPath('foo');

        $collection = new FolderCollapseStatusCollection();
        $collection->add($status);

        static::assertTrue($collection->isCollapsed('foo'));
        static::assertFalse($collection->isCollapsed('invalid'));
    }
}
