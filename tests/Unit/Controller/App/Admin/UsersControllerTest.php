<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Controller\App\Admin;

use DR\Review\Controller\AbstractController;
use DR\Review\Controller\App\Admin\UsersController;
use DR\Review\Tests\AbstractControllerTestCase;
use DR\Review\ViewModel\App\User\UsersViewModel;
use DR\Review\ViewModelProvider\UserViewModelProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @extends AbstractControllerTestCase<UsersController>
 */
#[CoversClass(UsersController::class)]
class UsersControllerTest extends AbstractControllerTestCase
{
    private UserViewModelProvider&MockObject $modelProvider;

    public function setUp(): void
    {
        $this->modelProvider = $this->createMock(UserViewModelProvider::class);
        parent::setUp();
    }

    public function testInvoke(): void
    {
        $viewModel = new UsersViewModel([], []);
        $this->modelProvider->expects($this->once())->method('getUsersViewModel')->willReturn($viewModel);

        static::assertSame(['usersViewModel' => $viewModel], ($this->controller)());
    }

    public function getController(): AbstractController
    {
        return new UsersController($this->modelProvider);
    }
}
