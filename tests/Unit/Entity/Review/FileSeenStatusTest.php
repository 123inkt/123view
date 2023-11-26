<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Entity\Review;

use DR\Review\Entity\Review\FileSeenStatus;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(FileSeenStatus::class)]
class FileSeenStatusTest extends AbstractTestCase
{
    public function testAccessorPairs(): void
    {
        static::assertAccessorPairs(FileSeenStatus::class);
    }
}
