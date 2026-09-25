<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Entity\Review;

use DR\Review\Entity\Review\CommentVisibilityEnum;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CommentVisibilityEnum::class)]
class CommentVisibilityTest extends AbstractTestCase
{
    public function testValues(): void
    {
        static::assertSame(['all', 'unresolved', 'none'], CommentVisibilityEnum::values());
    }
}
