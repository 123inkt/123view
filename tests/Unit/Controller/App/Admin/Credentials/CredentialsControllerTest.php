<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Controller\App\Admin\Credentials;

use DR\Review\Controller\AbstractController;
use DR\Review\Controller\App\Admin\Credentials\CredentialsController;
use DR\Review\Tests\AbstractControllerTestCase;
use DR\Review\ViewModel\App\Admin\CredentialsViewModel;
use DR\Review\ViewModelProvider\CredentialsViewModelProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @extends AbstractControllerTestCase<CredentialsController>
 */
#[CoversClass(CredentialsController::class)]
class CredentialsControllerTest extends AbstractControllerTestCase
{
    private CredentialsViewModelProvider&MockObject $viewModelProvider;

    protected function setUp(): void
    {
        $this->viewModelProvider = $this->createMock(CredentialsViewModelProvider::class);
        parent::setUp();
    }

    public function testInvoke(): void
    {
        $viewModel = $this->createMock(CredentialsViewModel::class);

        $this->viewModelProvider->expects(self::once())->method('getCredentialsViewModel')->willReturn($viewModel);

        static::assertSame(['credentialsViewModel' => $viewModel], ($this->controller)());
    }

    public function getController(): AbstractController
    {
        return new CredentialsController($this->viewModelProvider);
    }
}
