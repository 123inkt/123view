<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Entity\Review;

use DR\Review\Entity\Review\FolderCollapseStatus;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(FolderCollapseStatus::class)]
class FolderCollapseStatusTest extends AbstractTestCase
{
    public function testAccessorPairs(): void
    {
        static::assertAccessorPairs(FolderCollapseStatus::class);
    }
}
