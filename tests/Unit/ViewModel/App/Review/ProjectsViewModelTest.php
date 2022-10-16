<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\ViewModel\App\Review;

use DR\GitCommitNotification\Tests\AbstractTestCase;
use DR\GitCommitNotification\ViewModel\App\Review\ProjectsViewModel;

/**
 * @coversDefaultClass \DR\GitCommitNotification\ViewModel\App\Review\ProjectsViewModel
 * @covers ::__construct
 */
class ProjectsViewModelTest extends AbstractTestCase
{
    /**
     * @covers ::getRepositories
     * @covers ::getForm
     */
    public function testAccessorPairs(): void
    {
        static::assertAccessorPairs(ProjectsViewModel::class);
    }
}
