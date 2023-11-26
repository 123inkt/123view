<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Entity\Review;

use DR\Review\Entity\Review\UserMention;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(UserMention::class)]
class UserMentionTest extends AbstractTestCase
{
    public function testAccessorPairs(): void
    {
        static::assertAccessorPairs(UserMention::class);
    }
}
