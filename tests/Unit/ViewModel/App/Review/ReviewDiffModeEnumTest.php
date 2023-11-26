<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\ViewModel\App\Review;

use DR\Review\Tests\AbstractTestCase;
use DR\Review\ViewModel\App\Review\ReviewDiffModeEnum;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ReviewDiffModeEnum::class)]
class ReviewDiffModeEnumTest extends AbstractTestCase
{
    public function testValues(): void
    {
        static::assertSame(['side-by-side', 'unified', 'inline'], ReviewDiffModeEnum::values());
    }
}
