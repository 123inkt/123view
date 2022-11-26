<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\ViewModelProvider;

use Doctrine\ORM\Tools\Pagination\Paginator;
use DR\GitCommitNotification\Entity\Config\Repository;
use DR\GitCommitNotification\Entity\Review\CodeReview;
use DR\GitCommitNotification\Entity\Review\Revision;
use DR\GitCommitNotification\Form\Review\DetachRevisionsFormType;
use DR\GitCommitNotification\Repository\Review\RevisionRepository;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use DR\GitCommitNotification\ViewModelProvider\RevisionViewModelProvider;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Form\FormFactoryInterface;

/**
 * @coversDefaultClass \DR\GitCommitNotification\ViewModelProvider\RevisionViewModelProvider
 * @covers ::__construct
 */
class RevisionViewModelProviderTest extends AbstractTestCase
{
    private RevisionRepository&MockObject   $revisionRepository;
    private FormFactoryInterface&MockObject $formFactory;
    private RevisionViewModelProvider       $provider;

    public function setUp(): void
    {
        parent::setUp();
        $this->revisionRepository = $this->createMock(RevisionRepository::class);
        $this->formFactory        = $this->createMock(FormFactoryInterface::class);
        $this->provider           = new RevisionViewModelProvider($this->revisionRepository, $this->formFactory);
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
        $revision = new Revision();
        $review   = new CodeReview();
        $review->setId(123);

        $this->formFactory->expects(self::once())
            ->method('create')
            ->with(DetachRevisionsFormType::class, null, ['reviewId' => 123, 'revisions' => [$revision]]);

        $viewModel = $this->provider->getRevisionViewModel($review, [$revision]);
        static::assertSame([$revision], $viewModel->revisions);
    }
}
