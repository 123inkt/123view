<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Controller\App\Project;

use DR\Review\Controller\AbstractController;
use DR\Review\Controller\App\Project\ProjectsController;
use DR\Review\Tests\AbstractControllerTestCase;
use DR\Review\ViewModel\App\Project\ProjectsViewModel;
use DR\Review\ViewModelProvider\ProjectsViewModelProvider;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @coversDefaultClass \DR\Review\Controller\App\Project\ProjectsController
 * @covers ::__construct
 */
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

    /**
     * @covers ::__invoke
     */
    public function testInvoke(): void
    {
        $viewModel = $this->createMock(ProjectsViewModel::class);

        $this->viewModelProvider->expects(self::once())->method('getProjectsViewModel')->willReturn($viewModel);
        $this->translator->expects(self::once())->method('trans')->with('projects')->willReturn('Projects');

        $result = ($this->controller)();
        static::assertSame('Projects', $result['page_title']);
        static::assertSame($viewModel, $result['projectsModel']);
    }

    public function getController(): AbstractController
    {
        return new ProjectsController($this->viewModelProvider, $this->translator);
    }
}
