<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Controller\App\Project;

use DR\Review\Controller\AbstractController;
use DR\Review\Controller\App\Project\ProjectsController;
use DR\Review\Tests\AbstractControllerTestCase;
use DR\Review\ViewModel\App\Project\ProjectsViewModel;
use DR\Review\ViewModelProvider\ProjectsViewModelProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @extends AbstractControllerTestCase<ProjectsController>
 */
#[CoversClass(ProjectsController::class)]
class ProjectsControllerTest extends AbstractControllerTestCase
{
    private ProjectsViewModelProvider&MockObject $viewModelProvider;
    private TranslatorInterface&MockObject       $translator;

    public function setUp(): void
    {
        $this->viewModelProvider = $this->createMock(ProjectsViewModelProvider::class);
        $this->translator        = $this->createMock(TranslatorInterface::class);
        parent::setUp();
    }

    public function testInvoke(): void
    {
        $request   = new Request(['search' => 'search']);
        $viewModel = $this->createMock(ProjectsViewModel::class);

        $this->viewModelProvider->expects($this->once())->method('getProjectsViewModel')->with('search')->willReturn($viewModel);
        $this->translator->expects($this->once())->method('trans')->with('projects')->willReturn('Projects');

        $result = ($this->controller)($request);
        static::assertSame('Projects', $result['page_title']);
        static::assertSame($viewModel, $result['projectsModel']);
    }

    public function getController(): AbstractController
    {
        return new ProjectsController($this->viewModelProvider, $this->translator);
    }
}
