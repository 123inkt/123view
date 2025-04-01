<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Controller\App\Notification;

use DR\Review\Controller\AbstractController;
use DR\Review\Controller\App\Notification\RuleHistoryController;
use DR\Review\Controller\App\Notification\RuleNotificationMarkAsReadController;
use DR\Review\Entity\Notification\Rule;
use DR\Review\Repository\Config\RuleNotificationRepository;
use DR\Review\Tests\AbstractControllerTestCase;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @extends AbstractControllerTestCase<RuleNotificationMarkAsReadController>
 */
#[CoversClass(RuleNotificationMarkAsReadController::class)]
class RuleNotificationMarkAsReadControllerTest extends AbstractControllerTestCase
{
    private RuleNotificationRepository&MockObject $notificationRepository;

    #[Override]
    protected function setUp(): void
    {
        $this->notificationRepository = $this->createMock(RuleNotificationRepository::class);
        parent::setUp();
    }

    public function testInvoke(): void
    {
        $rule = new Rule();

        $this->notificationRepository->expects(self::once())->method('markAsRead')->with($rule);
        $this->expectRefererRedirect(RuleHistoryController::class);

        ($this->controller)($rule);
    }

    #[Override]
    public function getController(): AbstractController
    {
        return new RuleNotificationMarkAsReadController($this->notificationRepository);
    }
}
