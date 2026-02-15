<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\ViewModel\App\Project;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Tests\AbstractTestCase;
use DR\Review\ViewModel\App\Project\ProjectsViewModel;
use DR\Review\ViewModel\App\Review\Timeline\TimelineViewModel;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ProjectsViewModel::class)]
class ProjectsViewModelTest extends AbstractTestCase
{
    private TimelineViewModel $viewModel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->viewModel = static::createStub(TimelineViewModel::class);
    }

    public function testGetRepositories(): void
    {
        $repositoryA = new Repository();
        $repositoryA->setFavorite(true);
        $repositoryB = new Repository();
        $repositoryB->setFavorite(false);

        $viewModel = new ProjectsViewModel([$repositoryA, $repositoryB], [], $this->viewModel, 'search');
        static::assertSame([$repositoryA], $viewModel->getFavoriteRepositories());
        static::assertSame([$repositoryB], $viewModel->getRegularRepositories());
    }
}
