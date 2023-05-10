<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Controller\App\Project;

use DR\Review\Controller\AbstractController;
use DR\Review\Controller\App\Project\ProjectBranchesController;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Tests\AbstractControllerTestCase;
use DR\Review\ViewModel\App\Project\ProjectBranchesViewModel;
use DR\Review\ViewModelProvider\ProjectBranchesViewModelProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Contracts\Translation\TranslatorInterface;

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

    /**
     * @covers ::__invoke
     */
    public function testInvoke(): void
    {
        $repository = new Repository();
        $viewModel  = $this->createMock(ProjectBranchesViewModel::class);

        $this->viewModelProvider->expects(self::once())->method('getProjectBranchesViewModel')->with($repository)->willReturn($viewModel);
        $this->translator->expects(self::once())->method('trans')->with('branches')->willReturn('Branches');

        $result = ($this->controller)($repository);
        static::assertSame('Branches', $result['page_title']);
        static::assertSame($viewModel, $result['branchesViewModel']);
    }

    public function getController(): AbstractController
    {
        return new ProjectBranchesController($this->translator, $this->viewModelProvider);
    }
}
