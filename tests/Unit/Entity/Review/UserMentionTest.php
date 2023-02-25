<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Entity\Review;

use DR\Review\Entity\Review\UserMention;
use DR\Review\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\Review\Entity\Review\UserMention
 */
class UserMentionTest extends AbstractTestCase
{
    /**
     * @covers ::getComment
     * @covers ::setComment
     * @covers ::getUserId
     * @covers ::setUserId
     */
    public function testAccessorPairs(): void
    {
        static::assertAccessorPairs(UserMention::class);
    }
}
