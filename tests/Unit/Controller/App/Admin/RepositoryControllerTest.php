<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Controller\App\Admin;

use DR\Review\Controller\AbstractController;
use DR\Review\Controller\App\Admin\RepositoriesController;
use DR\Review\Controller\App\Admin\RepositoryController;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Form\Repository\EditRepositoryFormType;
use DR\Review\Message\Repository\RepositoryUpdated;
use DR\Review\Repository\Config\RepositoryRepository;
use DR\Review\Tests\AbstractControllerTestCase;
use DR\Review\ViewModel\App\Admin\EditRepositoryViewModel;
use PHPUnit\Framework\MockObject\MockObject;
use stdClass;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @coversDefaultClass \DR\Review\Controller\App\Admin\RepositoryController
 * @covers ::__construct
 */
class RepositoryControllerTest extends AbstractControllerTestCase
{
    private RepositoryRepository&MockObject $repositoryRepository;
    private MessageBusInterface&MockObject  $bus;
    private Envelope                        $envelope;

    protected function setUp(): void
    {
        $this->envelope             = new Envelope(new stdClass(), []);
        $this->bus                  = $this->createMock(MessageBusInterface::class);
        $this->repositoryRepository = $this->createMock(RepositoryRepository::class);
        parent::setUp();
    }

    /**
     * @covers ::__invoke
     */
    public function testInvokeRepositoryNotFound(): void
    {
        $request = new Request(attributes: ['id' => 123]);

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('Repository not found');
        ($this->controller)($request, null);
    }

    /**
     * @covers ::__invoke
     */
    public function testInvokeFormNotSubmitted(): void
    {
        $request    = new Request();
        $repository = new Repository();
        $repository->setId(123);

        $formView = $this->createMock(FormView::class);

        $this->expectCreateForm(EditRepositoryFormType::class, ['repository' => $repository])
            ->handleRequest($request)
            ->isSubmittedWillReturn(false)
            ->createViewWillReturn($formView);

        $actual = ($this->controller)($request, $repository);

        static::assertEquals(['editRepositoryModel' => new EditRepositoryViewModel($repository, $formView)], $actual);
    }

    /**
     * @covers ::__invoke
     */
    public function testInvokeFormIsSubmitted(): void
    {
        $request    = new Request();
        $repository = new Repository();
        $repository->setId(123);

        $this->expectCreateForm(EditRepositoryFormType::class, ['repository' => $repository])
            ->handleRequest($request)
            ->isSubmittedWillReturn(true)
            ->isValidWillReturn(true);
        $this->repositoryRepository->expects(self::once())->method('save')->with($repository, true);
        $this->bus->expects(self::once())->method('dispatch')->with(new RepositoryUpdated(123))->willReturn($this->envelope);
        $this->expectAddFlash('success', 'repository.successful.saved');
        $this->expectRedirectToRoute(RepositoriesController::class)->willReturn('url');

        ($this->controller)($request, $repository);
    }

    public function getController(): AbstractController
    {
        return new RepositoryController($this->repositoryRepository, $this->bus);
    }
}
