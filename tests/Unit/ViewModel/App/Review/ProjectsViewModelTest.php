<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\ViewModel\App\Review;

use DR\Review\Tests\AbstractTestCase;
use DR\Review\ViewModel\App\Review\ProjectsViewModel;

/**
 * @coversDefaultClass \DR\Review\ViewModel\App\Review\ProjectsViewModel
 * @covers ::__construct
 */
class ProjectsViewModelTest extends AbstractTestCase
{
    /**
     * @covers ::<public>
     */
    public function testAccessorPairs(): void
    {
        static::assertAccessorPairs(ProjectsViewModel::class);
    }
}
