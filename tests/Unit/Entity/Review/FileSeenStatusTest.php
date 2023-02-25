<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Entity\Review;

use DR\Review\Entity\Review\FileSeenStatus;
use DR\Review\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\Review\Entity\Review\FileSeenStatus
 */
class FileSeenStatusTest extends AbstractTestCase
{
    /**
     * @covers ::setId
     * @covers ::getId
     * @covers ::getFilePath
     * @covers ::setFilePath
     * @covers ::getCreateTimestamp
     * @covers ::setCreateTimestamp
     * @covers ::getUser
     * @covers ::setUser
     * @covers ::getReview
     * @covers ::setReview
     */
    public function testAccessorPairs(): void
    {
        static::assertAccessorPairs(FileSeenStatus::class);
    }
}
