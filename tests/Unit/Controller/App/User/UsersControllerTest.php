<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Controller\App\User;

use DR\Review\Controller\AbstractController;
use DR\Review\Controller\App\User\UsersController;
use DR\Review\Tests\AbstractControllerTestCase;
use DR\Review\ViewModel\App\User\UsersViewModel;
use DR\Review\ViewModelProvider\UserViewModelProvider;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @coversDefaultClass \DR\Review\Controller\App\User\UsersController
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
