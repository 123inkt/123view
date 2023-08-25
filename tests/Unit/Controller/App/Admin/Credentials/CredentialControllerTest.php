<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Controller\App\Admin\Credentials;

use DR\Review\Controller\AbstractController;
use DR\Review\Controller\App\Admin\Credentials\CredentialController;
use DR\Review\Controller\App\Admin\Credentials\CredentialsController;
use DR\Review\Entity\Repository\RepositoryCredential;
use DR\Review\Form\Repository\Credential\EditCredentialFormType;
use DR\Review\Repository\Config\RepositoryCredentialRepository;
use DR\Review\Service\Git\Remote\GitRemoteService;
use DR\Review\Tests\AbstractControllerTestCase;
use DR\Review\ViewModel\App\Admin\EditCredentialViewModel;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[CoversClass(CredentialController::class)]
class CredentialControllerTest extends AbstractControllerTestCase
{
    private RepositoryCredentialRepository&MockObject $repository;
    private GitRemoteService&MockObject               $gitRemoteService;

    protected function setUp(): void
    {
        $this->gitRemoteService = $this->createMock(GitRemoteService::class);
        $this->repository       = $this->createMock(RepositoryCredentialRepository::class);
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

        $this->expectCreateForm(EditCredentialFormType::class, ['credential' => $credential])
            ->handleRequest($request)
            ->isSubmittedWillReturn(true)
            ->isValidWillReturn(true);

        $this->repository->expects(static::once())->method('save')->with($credential, true);
        $this->gitRemoteService->expects(static::once())->method('updateRemoteUrls')->with($credential);
        $this->expectAddFlash('success', 'credential.successful.saved');
        $this->expectRedirectToRoute(CredentialsController::class)->willReturn('url');

        ($this->controller)($request, $credential);
    }

    public function getController(): AbstractController
    {
        return new CredentialController($this->repository, $this->gitRemoteService);
    }
}
