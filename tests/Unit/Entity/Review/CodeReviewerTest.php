<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Entity\Review;

use DR\Review\Entity\Review\CodeReviewer;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CodeReviewer::class)]
class CodeReviewerTest extends AbstractTestCase
{
    public function testAccessorPairs(): void
    {
        static::assertNull((new CodeReviewer())->getId());
        static::assertAccessorPairs(CodeReviewer::class);
    }
}
