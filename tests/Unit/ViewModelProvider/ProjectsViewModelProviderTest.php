<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\ViewModelProvider;

use Doctrine\DBAL\Exception;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\User\User;
use DR\Review\Repository\Config\RepositoryRepository;
use DR\Review\Repository\Revision\RevisionRepository;
use DR\Review\Tests\AbstractTestCase;
use DR\Review\ViewModel\App\Review\Timeline\TimelineViewModel;
use DR\Review\ViewModelProvider\ProjectsViewModelProvider;
use DR\Review\ViewModelProvider\ReviewTimelineViewModelProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(ProjectsViewModelProvider::class)]
class ProjectsViewModelProviderTest extends AbstractTestCase
{
    private RepositoryRepository&MockObject            $repositoryRepository;
    private RevisionRepository&MockObject              $revisionRepository;
    private ReviewTimelineViewModelProvider&MockObject $viewModelProvider;
    private ProjectsViewModelProvider                  $provider;
    private User                                       $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user                 = new User();
        $this->repositoryRepository = $this->createMock(RepositoryRepository::class);
        $this->revisionRepository   = $this->createMock(RevisionRepository::class);
        $this->viewModelProvider    = $this->createMock(ReviewTimelineViewModelProvider::class);
        $this->provider             = new ProjectsViewModelProvider(
            $this->repositoryRepository,
            $this->revisionRepository,
            $this->viewModelProvider,
            $this->user
        );
    }

    /**
     * @throws Exception
     */
    public function testGetProjectsViewModel(): void
    {
        $repository = new Repository();
        $repository->setDisplayName('repository');
        $timeline   = $this->createMock(TimelineViewModel::class);

        $this->repositoryRepository->expects($this->once())
            ->method('findBy')
            ->with(['active' => 1], ['displayName' => 'ASC'])
            ->willReturn([$repository]);
        $this->revisionRepository->expects($this->once())->method('getRepositoryRevisionCount')->willReturn([5 => 6]);
        $this->viewModelProvider->expects($this->once())->method('getTimelineViewModelForFeed')->with($this->user)->willReturn($timeline);

        $viewModel = $this->provider->getProjectsViewModel('repo');
        static::assertSame([$repository], $viewModel->repositories);
        static::assertSame([5 => 6], $viewModel->revisionCount);
        static::assertSame($timeline, $viewModel->timeline);
    }
}
