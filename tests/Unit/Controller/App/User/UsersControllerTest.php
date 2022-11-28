<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Controller\App\User;

use DR\GitCommitNotification\Controller\AbstractController;
use DR\GitCommitNotification\Controller\App\User\UsersController;
use DR\GitCommitNotification\Tests\AbstractControllerTestCase;
use DR\GitCommitNotification\ViewModel\App\User\UsersViewModel;
use DR\GitCommitNotification\ViewModelProvider\UserViewModelProvider;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Controller\App\User\UsersController
 * @covers ::__construct
 */
class UsersControllerTest extends AbstractControllerTestCase
{
    private UserViewModelProvider&MockObject $modelProvider;

    public function setUp(): void
    {
        $this->modelProvider = $this->createMock(UserViewModelProvider::class);
        parent::setUp();
    }

    /**
     * @covers ::__invoke
     */
    public function testInvoke(): void
    {
        $viewModel = new UsersViewModel([], []);
        $this->modelProvider->expects(self::once())->method('getUsersViewModel')->willReturn($viewModel);

        static::assertSame(['usersViewModel' => $viewModel], ($this->controller)());
    }

    public function getController(): AbstractController
    {
        return new UsersController($this->modelProvider);
    }
}
