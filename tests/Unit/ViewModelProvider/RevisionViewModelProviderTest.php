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
use DR\Review\Repository\Revision\RevisionRepository;
use DR\Review\Service\Revision\RevisionVisibilityService;
use DR\Review\Tests\AbstractTestCase;
use DR\Review\ViewModelProvider\RevisionViewModelProvider;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

/**
 * @coversDefaultClass \DR\Review\ViewModelProvider\RevisionViewModelProvider
 * @covers ::__construct
 */
class RevisionViewModelProviderTest extends AbstractTestCase
{
    private RevisionRepository&MockObject        $revisionRepository;
    private RevisionVisibilityService&MockObject $visibilityService;
    private FormFactoryInterface&MockObject      $formFactory;
    private RevisionViewModelProvider            $provider;
    private User                                 $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->revisionRepository = $this->createMock(RevisionRepository::class);
        $this->visibilityService  = $this->createMock(RevisionVisibilityService::class);
        $this->formFactory        = $this->createMock(FormFactoryInterface::class);
        $this->user               = new User();
        $this->provider           = new RevisionViewModelProvider(
            $this->revisionRepository,
            $this->visibilityService,
            $this->formFactory,
            $this->user
        );
    }

    /**
     * @covers ::getRevisionsViewModel
     */
    public function testGetRevisionsViewModel(): void
    {
        $page        = 10;
        $searchQuery = 'search';
        $repository  = new Repository();
        $repository->setId(123);
        $paginator = $this->createMock(Paginator::class);

        $this->revisionRepository->expects(self::once())
            ->method('getPaginatorForSearchQuery')
            ->with(123, $page, $searchQuery, false)
            ->willReturn($paginator);

        $viewModel = $this->provider->getRevisionsViewModel($repository, $page, $searchQuery, false);
        static::assertSame($page, $viewModel->paginator->page);
    }

    /**
     * @covers ::getRevisionViewModel
     */
    public function testGetRevisionViewModel(): void
    {
        $revision   = new Revision();
        $visibility = new RevisionVisibility();
        $review     = new CodeReview();
        $review->setId(123);

        $this->visibilityService->expects(self::once())
            ->method('getRevisionVisibilities')
            ->with($review, [$revision], $this->user)
            ->willReturn([$visibility]);
        $this->formFactory->expects(self::exactly(2))
            ->method('create')
            ->will(
                static::onConsecutiveCalls(
                    [DetachRevisionsFormType::class, null, ['reviewId' => 123, 'revisions' => [$revision]]],
                    [RevisionVisibilityFormType::class, ['visibilities' => [$visibility]], ['reviewId' => 123]],
                )
            )
            ->willReturn($this->createMock(FormInterface::class));

        $viewModel = $this->provider->getRevisionViewModel($review, [$revision]);
        static::assertSame([$revision], $viewModel->revisions);
    }

    /**
     * @covers ::getRevisionViewModel
     */
    public function testGetRevisionViewModelBranchReview(): void
    {
        $revision   = new Revision();
        $visibility = new RevisionVisibility();
        $review     = new CodeReview();
        $review->setId(123);
        $review->setType(CodeReviewType::BRANCH);

        $this->visibilityService->expects(self::once())
            ->method('getRevisionVisibilities')
            ->with($review, [$revision], $this->user)
            ->willReturn([$visibility]);
        $this->formFactory->expects(self::never())->method('create');

        $viewModel = $this->provider->getRevisionViewModel($review, [$revision]);
        static::assertSame([$revision], $viewModel->revisions);
    }
}
