<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Revision;

use DR\Review\Entity\Revision\Revision;
use DR\Review\Service\Revision\RevisionSorter;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(RevisionSorter::class)]
class RevisionSorterTest extends AbstractTestCase
{
    public function testSortByTimestamp(): void
    {
        $revisionA = (new Revision())->setCreateTimestamp(300);
        $revisionB = (new Revision())->setCreateTimestamp(100);
        $revisionC = (new Revision())->setCreateTimestamp(200);

        $sorter = new RevisionSorter();
        $result = $sorter->sort([$revisionA, $revisionB, $revisionC]);

        static::assertSame([$revisionB, $revisionC, $revisionA], $result);
    }

    public function testSortByUuid(): void
    {
        $revisionA = (new Revision())->setSort('cccccccc-0000-0000-0000-000000000000');
        $revisionB = (new Revision())->setSort('aaaaaaaa-0000-0000-0000-000000000000');
        $revisionC = (new Revision())->setSort('bbbbbbbb-0000-0000-0000-000000000000');

        $sorter = new RevisionSorter();
        $result = $sorter->sort([$revisionA, $revisionB, $revisionC]);

        static::assertSame([$revisionB, $revisionC, $revisionA], $result);
    }
}
