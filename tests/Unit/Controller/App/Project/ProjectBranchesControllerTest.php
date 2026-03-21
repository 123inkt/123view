<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Controller\App\Project;

use DR\Review\Controller\AbstractController;
use DR\Review\Controller\App\Project\ProjectBranchesController;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Request\Project\ProjectBranchRequest;
use DR\Review\Tests\AbstractControllerTestCase;
use DR\Review\ViewModel\App\Project\ProjectBranchesViewModel;
use DR\Review\ViewModelProvider\ProjectBranchesViewModelProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @extends AbstractControllerTestCase<ProjectBranchesController>
 */
#[CoversClass(ProjectBranchesController::class)]
class ProjectBranchesControllerTest extends AbstractControllerTestCase
{
    private ProjectBranchesViewModelProvider&MockObject $viewModelProvider;
    private TranslatorInterface&MockObject              $translator;

    public function setUp(): void
    {
        $this->viewModelProvider = $this->createMock(ProjectBranchesViewModelProvider::class);
        $this->translator        = $this->createMock(TranslatorInterface::class);
        parent::setUp();
    }

    public function testInvoke(): void
    {
        $repository = new Repository();
        $viewModel  = static::createStub(ProjectBranchesViewModel::class);

        $request = $this->createMock(ProjectBranchRequest::class);
        $request->expects($this->once())->method('getSearchQuery')->willReturn('search');

        $this->viewModelProvider->expects($this->once())->method('getProjectBranchesViewModel')->with($repository, 'search')->willReturn($viewModel);
        $this->translator->expects($this->once())->method('trans')->with('branches')->willReturn('Branches');

        $result = ($this->controller)($request, $repository);
        static::assertSame('Branches', $result['page_title']);
        static::assertSame($viewModel, $result['branchesViewModel']);
    }

    public function getController(): AbstractController
    {
        return new ProjectBranchesController($this->translator, $this->viewModelProvider);
    }
}
