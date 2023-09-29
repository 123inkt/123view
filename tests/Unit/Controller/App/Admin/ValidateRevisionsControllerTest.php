<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Controller\App\Admin;

use DR\Review\Controller\AbstractController;
use DR\Review\Controller\App\Admin\RepositoriesController;
use DR\Review\Controller\App\Admin\ValidateRevisionsController;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Message\Revision\ValidateRevisionsMessage;
use DR\Review\Repository\Config\RepositoryRepository;
use DR\Review\Tests\AbstractControllerTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use stdClass;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

#[CoversClass(ValidateRevisionsController::class)]
class ValidateRevisionsControllerTest extends AbstractControllerTestCase
{
    private MessageBusInterface&MockObject $messageBus;
    private Envelope                       $envelope;

    protected function setUp(): void
    {
        $this->envelope             = new Envelope(new stdClass(), []);
        $this->messageBus           = $this->createMock(MessageBusInterface::class);
        $this->repositoryRepository = $this->createMock(RepositoryRepository::class);
        parent::setUp();
    }

    public function testInvoke(): void
    {
        $repository = new Repository();
        $repository->setId(123);

        $this->messageBus->expects(self::once())->method('dispatch')->with(new ValidateRevisionsMessage(123))->willReturn($this->envelope);
        $this->expectAddFlash('success', 'repository.schedule.validate_revisions');
        $this->expectRedirectToRoute(RepositoriesController::class)->willReturn('url');

        ($this->controller)($repository);
    }

    public function getController(): AbstractController
    {
        return new ValidateRevisionsController($this->messageBus);
    }
}
