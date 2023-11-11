<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Controller\App\Notification;

use DR\Review\Controller\AbstractController;
use DR\Review\Controller\App\Notification\RuleHistoryController;
use DR\Review\Tests\AbstractControllerTestCase;
use DR\Review\ViewModel\App\Notification\RuleNotificationViewModel;
use DR\Review\ViewModelProvider\RuleNotificationViewModelProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Request;

#[CoversClass(RuleHistoryController::class)]
class RuleHistoryControllerTest extends AbstractControllerTestCase
{
    private RuleNotificationViewModelProvider&MockObject $viewModelProvider;

    protected function setUp(): void
    {
        $this->viewModelProvider = $this->createMock(RuleNotificationViewModelProvider::class);
        parent::setUp();
    }

    public function testInvokeWithoutQueryParams(): void
    {
        $request   = new Request();
        $viewModel = $this->createMock(RuleNotificationViewModel::class);

        $this->viewModelProvider->expects(self::once())->method('getNotificationsViewModel')->with(null, false)->willReturn($viewModel);

        static::assertSame(['notificationViewModel' => $viewModel], ($this->controller)($request));
    }

    public function testInvokeWithQueryParams(): void
    {
        $request   = new Request(['ruleId' => 123, 'filter' => 'unread']);
        $viewModel = $this->createMock(RuleNotificationViewModel::class);

        $this->viewModelProvider->expects(self::once())->method('getNotificationsViewModel')->with(123, true)->willReturn($viewModel);

        static::assertSame(['notificationViewModel' => $viewModel], ($this->controller)($request));
    }

    public function getController(): AbstractController
    {
        return new RuleHistoryController($this->viewModelProvider);
    }
}
