<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Controller\App\Admin;

use DR\Review\Controller\AbstractController;
use DR\Review\Controller\App\Admin\RepositoriesController;
use DR\Review\Controller\App\Admin\ValidateRevisionsController;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Message\Revision\ValidateRevisionsMessage;
use DR\Review\Tests\AbstractControllerTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @extends AbstractControllerTestCase<ValidateRevisionsController>
 */
#[CoversClass(ValidateRevisionsController::class)]
class ValidateRevisionsControllerTest extends AbstractControllerTestCase
{
    private MessageBusInterface&MockObject $messageBus;

    protected function setUp(): void
    {
        $this->messageBus = $this->createMock(MessageBusInterface::class);
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
