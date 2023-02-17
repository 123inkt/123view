<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\ViewModel\App\Review\Timeline;

use DR\Review\Tests\AbstractTestCase;
use DR\Review\ViewModel\App\Review\Timeline\TimelineEntryViewModel;

/**
 * @coversDefaultClass \DR\Review\ViewModel\App\Review\Timeline\TimelineEntryViewModel
 * @covers ::__construct
 */
class TimelineEntryViewModelTest extends AbstractTestCase
{
    /**
     * @covers ::<public>
     */
    public function testAccessorPairs(): void
    {
        static::assertAccessorPairs(TimelineEntryViewModel::class);
    }
}
