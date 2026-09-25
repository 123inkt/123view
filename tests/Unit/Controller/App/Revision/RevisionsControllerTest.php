<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Controller\App\Revision;

use DR\Review\Controller\AbstractController;
use DR\Review\Controller\App\Revision\RevisionsController;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Model\Page\Breadcrumb;
use DR\Review\Service\Page\BreadcrumbFactory;
use DR\Review\Tests\AbstractControllerTestCase;
use DR\Review\ViewModel\App\Revision\RevisionsViewModel;
use DR\Review\ViewModelProvider\RevisionViewModelProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Request;

/**
 * @extends AbstractControllerTestCase<RevisionsController>
 */
#[CoversClass(RevisionsController::class)]
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

    public function testInvoke(): void
    {
        $request    = new Request(['search' => 'search', 'page' => '10']);
        $repository = new Repository();
        $breadcrumb = new Breadcrumb('label', 'url');
        $viewModel  = static::createStub(RevisionsViewModel::class);

        $this->breadcrumbFactory->expects($this->once())->method('createForReviews')->with($repository)->willReturn([$breadcrumb]);
        $this->viewModelProvider->expects($this->once())
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
