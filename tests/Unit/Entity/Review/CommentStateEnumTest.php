<?php

declare(strict_types=1);

namespace DR\Review\Tests\Unit\Entity\Review;

use DR\Review\Entity\Review\CommentStateEnum;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CommentStateEnum::class)]
class CommentStateEnumTest extends AbstractTestCase
{
    public function testValues(): void
    {
        static::assertSame(['open', 'resolved'], CommentStateEnum::values());
    }
}
