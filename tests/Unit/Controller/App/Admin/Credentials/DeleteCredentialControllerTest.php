<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Controller\App\Admin\Credentials;

use DR\Review\Controller\AbstractController;
use DR\Review\Controller\App\Admin\Credentials\CredentialsController;
use DR\Review\Controller\App\Admin\Credentials\DeleteCredentialController;
use DR\Review\Entity\Repository\RepositoryCredential;
use DR\Review\Repository\Config\RepositoryCredentialRepository;
use DR\Review\Tests\AbstractControllerTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @extends AbstractControllerTestCase<DeleteCredentialController>
 */
#[CoversClass(DeleteCredentialController::class)]
class DeleteCredentialControllerTest extends AbstractControllerTestCase
{
    private RepositoryCredentialRepository&MockObject $credentialRepository;

    protected function setUp(): void
    {
        $this->credentialRepository = $this->createMock(RepositoryCredentialRepository::class);
        parent::setUp();
    }

    public function testInvoke(): void
    {
        $credential = new RepositoryCredential();

        $this->credentialRepository->expects(self::once())->method('remove')->with($credential, true);
        $this->expectAddFlash('success', 'credential.successful.removed');
        $this->expectRefererRedirect(CredentialsController::class);

        ($this->controller)($credential);
    }

    public function getController(): AbstractController
    {
        return new DeleteCredentialController($this->credentialRepository);
    }
}
