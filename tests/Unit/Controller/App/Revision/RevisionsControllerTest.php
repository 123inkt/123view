<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Controller\App\Revision;

use DR\GitCommitNotification\Controller\AbstractController;
use DR\GitCommitNotification\Controller\App\Revision\RevisionsController;
use DR\GitCommitNotification\Entity\Repository\Repository;
use DR\GitCommitNotification\Model\Page\Breadcrumb;
use DR\GitCommitNotification\Service\Page\BreadcrumbFactory;
use DR\GitCommitNotification\Tests\AbstractControllerTestCase;
use DR\GitCommitNotification\ViewModel\App\Review\RevisionsViewModel;
use DR\GitCommitNotification\ViewModelProvider\RevisionViewModelProvider;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Request;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Controller\App\Revision\RevisionsController
 * @covers ::__construct
 */
class RevisionsControllerTest extends AbstractControllerTestCase
{
    private BreadcrumbFactory&MockObject         $breadcrumbFactory;
    private RevisionViewModelProvider&MockObject $viewModelProvider;

    public function setUp(): void
    {
        $this->breadcrumbFactory = $this->createMock(BreadcrumbFactory::class);
        $this->viewModelProvider = $this->createMock(RevisionViewModelProvider::class);
        parent::setUp();
    }

    /**
     * @covers ::__invoke
     */
    public function testInvoke(): void
    {
        $request    = new Request(['search' => 'search', 'page' => '10']);
        $repository = new Repository();
        $breadcrumb = new Breadcrumb('label', 'url');
        $viewModel  = $this->createMock(RevisionsViewModel::class);

        $this->breadcrumbFactory->expects(self::once())->method('createForReviews')->with($repository)->willReturn([$breadcrumb]);
        $this->viewModelProvider->expects(self::once())
            ->method('getRevisionsViewModel')
            ->with($repository, 10, 'search')
            ->willReturn($viewModel);

        $actual   = ($this->controller)($request, $repository);
        $expected = ['breadcrumbs' => [$breadcrumb], 'revisionsModel' => $viewModel];
        static::assertSame($expected, $actual);
    }

    public function getController(): AbstractController
    {
        return new RevisionsController($this->breadcrumbFactory, $this->viewModelProvider);
    }
}
