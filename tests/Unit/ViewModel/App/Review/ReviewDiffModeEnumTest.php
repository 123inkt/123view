<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\ViewModel\App\Review;

use DR\Review\Tests\AbstractTestCase;
use DR\Review\ViewModel\App\Review\ReviewDiffModeEnum;

/**
 * @coversDefaultClass \DR\Review\ViewModel\App\Review\ReviewDiffModeEnum
 * @covers ::__construct
 */
class ReviewDiffModeEnumTest extends AbstractTestCase
{
    /**
     * @covers ::values
     */
    public function testValues(): void
    {
        static::assertSame(['unified', 'inline'], ReviewDiffModeEnum::values());
    }
}
