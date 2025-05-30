<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Controller\App\Admin\Credentials;

use DR\Review\Controller\AbstractController;
use DR\Review\Controller\App\Admin\Credentials\CredentialController;
use DR\Review\Controller\App\Admin\Credentials\CredentialsController;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Repository\RepositoryCredential;
use DR\Review\Form\Repository\Credential\EditCredentialFormType;
use DR\Review\Message\Revision\RepositoryUpdatedMessage;
use DR\Review\Repository\Config\RepositoryCredentialRepository;
use DR\Review\Repository\Config\RepositoryRepository;
use DR\Review\Tests\AbstractControllerTestCase;
use DR\Review\ViewModel\App\Admin\EditCredentialViewModel;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @extends AbstractControllerTestCase<CredentialController>
 */
#[CoversClass(CredentialController::class)]
class CredentialControllerTest extends AbstractControllerTestCase
{
    private RepositoryCredentialRepository&MockObject $credentialRepository;
    private RepositoryRepository&MockObject           $repositoryRepository;
    private MessageBusInterface&MockObject            $messageBus;

    protected function setUp(): void
    {
        $this->messageBus           = $this->createMock(MessageBusInterface::class);
        $this->credentialRepository = $this->createMock(RepositoryCredentialRepository::class);
        $this->repositoryRepository = $this->createMock(RepositoryRepository::class);
        parent::setUp();
    }

    public function testInvokeNotFound(): void
    {
        $request = new Request(attributes: ['id' => 5]);

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('Credential not found');
        ($this->controller)($request, null);
    }

    public function testInvokeEditCredential(): void
    {
        $request    = new Request();
        $credential = new RepositoryCredential();

        $form = $this->createMock(FormView::class);

        $this->expectCreateForm(EditCredentialFormType::class, ['credential' => $credential])
            ->handleRequest($request)
            ->isSubmittedWillReturn(false)
            ->createViewWillReturn($form);

        $result = ($this->controller)($request, $credential);
        static::assertEquals(['editCredentialModel' => new EditCredentialViewModel($credential, $form)], $result);
    }

    public function testInvokeFormSubmit(): void
    {
        $request    = new Request();
        $credential = new RepositoryCredential();
        $credential->setId(123);
        $repository = (new Repository())->setId(456);

        $this->expectCreateForm(EditCredentialFormType::class, ['credential' => $credential])
            ->handleRequest($request)
            ->isSubmittedWillReturn(true)
            ->isValidWillReturn(true);

        $this->credentialRepository->expects($this->once())->method('save')->with($credential, true);
        $this->repositoryRepository->expects($this->once())->method('findBy')->with(['credential' => $credential])->willReturn([$repository]);
        $this->messageBus->expects($this->once())->method('dispatch')->with(new RepositoryUpdatedMessage(456))->willReturn($this->envelope);
        $this->expectAddFlash('success', 'credential.successful.saved');
        $this->expectRedirectToRoute(CredentialsController::class)->willReturn('url');

        ($this->controller)($request, $credential);
    }

    public function getController(): AbstractController
    {
        return new CredentialController($this->credentialRepository, $this->repositoryRepository, $this->messageBus);
    }
}
