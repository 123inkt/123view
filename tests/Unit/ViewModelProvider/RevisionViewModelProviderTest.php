<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\ViewModelProvider;

use Doctrine\ORM\Tools\Pagination\Paginator;
use DR\Review\Doctrine\Type\CodeReviewType;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Entity\Revision\RevisionVisibility;
use DR\Review\Entity\User\User;
use DR\Review\Form\Review\Revision\DetachRevisionsFormType;
use DR\Review\Form\Review\Revision\RevisionVisibilityFormType;
use DR\Review\Model\Review\RevisionFileChange;
use DR\Review\Repository\Revision\RevisionFileRepository;
use DR\Review\Repository\Revision\RevisionRepository;
use DR\Review\Service\Revision\RevisionVisibilityService;
use DR\Review\Service\User\UserEntityProvider;
use DR\Review\Tests\AbstractTestCase;
use DR\Review\ViewModelProvider\RevisionViewModelProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use function DR\PHPUnitExtensions\Mock\consecutive;

#[CoversClass(RevisionViewModelProvider::class)]
class RevisionViewModelProviderTest extends AbstractTestCase
{
    private RevisionRepository&MockObject        $revisionRepository;
    private RevisionVisibilityService&MockObject $visibilityService;
    private RevisionFileRepository&MockObject    $revisionFileRepository;
    private FormFactoryInterface&MockObject      $formFactory;
    private UserEntityProvider&MockObject       $userProvider;
    private RevisionViewModelProvider            $provider;
    private User                                 $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->revisionRepository     = $this->createMock(RevisionRepository::class);
        $this->visibilityService      = $this->createMock(RevisionVisibilityService::class);
        $this->revisionFileRepository = $this->createMock(RevisionFileRepository::class);
        $this->formFactory            = $this->createMock(FormFactoryInterface::class);
        $this->userProvider           = $this->createMock(UserEntityProvider::class);
        $this->user                   = new User();
        $this->provider               = new RevisionViewModelProvider(
            $this->revisionRepository,
            $this->visibilityService,
            $this->revisionFileRepository,
            $this->formFactory,
            $this->userProvider
        );
    }

    public function testGetRevisionsViewModel(): void
    {
        $page        = 10;
        $searchQuery = 'search';
        $repository  = new Repository();
        $repository->setId(123);
        $paginator = static::createStub(Paginator::class);

        $this->revisionRepository->expects($this->once())
            ->method('getPaginatorForSearchQuery')
            ->with(123, $page, $searchQuery, false)
            ->willReturn($paginator);
        $this->visibilityService->expects($this->never())->method('getRevisionVisibilities');
        $this->revisionFileRepository->expects($this->never())->method('getFileChanges');
        $this->formFactory->expects($this->never())->method('create');
        $this->userProvider->expects($this->never())->method('getCurrentUser');

        $viewModel = $this->provider->getRevisionsViewModel($repository, $page, $searchQuery, false);
        static::assertSame($page, $viewModel->paginator->page);
    }

    public function testGetRevisionViewModel(): void
    {
        $revision   = new Revision();
        $visibility = new RevisionVisibility();
        $fileChange = new RevisionFileChange(1, 2, 3, 4);
        $review     = new CodeReview();
        $review->setId(123);

        $this->userProvider->expects($this->once())
            ->method('getCurrentUser')
            ->willReturn($this->user);
        $this->visibilityService->expects($this->once())
            ->method('getRevisionVisibilities')
            ->with($review, [$revision], $this->user)
            ->willReturn([$visibility]);
        $this->revisionFileRepository->expects($this->once())->method('getFileChanges')->with([$revision])->willReturn([123 => $fileChange]);
        $this->formFactory->expects($this->exactly(2))
            ->method('create')
            ->with(
                ...consecutive(
                    [DetachRevisionsFormType::class, null, ['reviewId' => 123, 'revisions' => [$revision]]],
                    [RevisionVisibilityFormType::class, ['visibilities' => [$visibility]], ['reviewId' => 123]],
                )
            )
            ->willReturn(static::createStub(FormInterface::class));
        $this->revisionRepository->expects($this->never())->method('getPaginatorForSearchQuery');

        $viewModel = $this->provider->getRevisionViewModel($review, [$revision]);
        static::assertSame([$revision], $viewModel->revisions);
        static::assertSame([123 => $fileChange], $viewModel->fileChanges);
    }

    public function testGetRevisionViewModelBranchReview(): void
    {
        $revision   = new Revision();
        $visibility = new RevisionVisibility();
        $review     = new CodeReview();
        $review->setId(123);
        $review->setType(CodeReviewType::BRANCH);

        $this->userProvider->expects($this->once())
            ->method('getCurrentUser')
            ->willReturn($this->user);
        $this->visibilityService->expects($this->once())
            ->method('getRevisionVisibilities')
            ->with($review, [$revision], $this->user)
            ->willReturn([$visibility]);
        $this->formFactory->expects($this->once())
            ->method('create')
            ->with(RevisionVisibilityFormType::class, ['visibilities' => [$visibility]], ['reviewId' => 123])
            ->willReturn(static::createStub(FormInterface::class));
        $this->revisionRepository->expects($this->never())->method('getPaginatorForSearchQuery');
        $this->revisionFileRepository->expects($this->once())->method('getFileChanges');

        $viewModel = $this->provider->getRevisionViewModel($review, [$revision]);
        static::assertSame([$revision], $viewModel->revisions);
    }
}
