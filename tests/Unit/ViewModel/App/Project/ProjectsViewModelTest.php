<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\ViewModel\App\Project;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Tests\AbstractTestCase;
use DR\Review\ViewModel\App\Project\ProjectsViewModel;
use DR\Review\ViewModel\App\Review\Timeline\TimelineViewModel;

/**
 * @coversDefaultClass \DR\Review\ViewModel\App\Project\ProjectsViewModel
 * @covers ::__construct
 */
class ProjectsViewModelTest extends AbstractTestCase
{
    private TimelineViewModel $viewModel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->viewModel = $this->createMock(TimelineViewModel::class);
    }

    /**
     * @covers ::getFavoriteRepositories
     * @covers ::getRegularRepositories
     */
    public function testGetRepositories(): void
    {
        $repositoryA = new Repository();
        $repositoryA->setFavorite(true);
        $repositoryB = new Repository();
        $repositoryB->setFavorite(false);

        $viewModel = new ProjectsViewModel([$repositoryA, $repositoryB], [], $this->viewModel);
        static::assertSame([$repositoryA], $viewModel->getFavoriteRepositories());
        static::assertSame([$repositoryB], $viewModel->getRegularRepositories());
    }
}
