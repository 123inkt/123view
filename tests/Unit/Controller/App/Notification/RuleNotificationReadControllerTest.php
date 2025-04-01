<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Controller\App\Notification;

use DR\Review\Controller\AbstractController;
use DR\Review\Controller\App\Notification\RuleNotificationReadController;
use DR\Review\Entity\Notification\RuleNotification;
use DR\Review\Repository\Config\RuleNotificationRepository;
use DR\Review\Service\Notification\RuleNotificationTokenGenerator;
use DR\Review\Tests\AbstractControllerTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @extends AbstractControllerTestCase<RuleNotificationReadController>
 */
#[CoversClass(RuleNotificationReadController::class)]
class RuleNotificationReadControllerTest extends AbstractControllerTestCase
{
    private RuleNotificationTokenGenerator&MockObject $tokenGenerator;
    private RuleNotificationRepository&MockObject     $notificationRepository;

    protected function setUp(): void
    {
        $this->tokenGenerator         = $this->createMock(RuleNotificationTokenGenerator::class);
        $this->notificationRepository = $this->createMock(RuleNotificationRepository::class);
        parent::setUp();
    }

    public function testInvoke(): void
    {
        $notification = new RuleNotification();
        $notification->setRead(false);

        $this->tokenGenerator->expects(self::once())->method('generate')->with($notification)->willReturn('token');
        $this->notificationRepository->expects(self::once())->method('save')->with($notification, true);

        ($this->controller)($notification, 'token');
    }

    public function testInvokeAlreadyRead(): void
    {
        $notification = new RuleNotification();
        $notification->setRead(true);

        $this->tokenGenerator->expects(self::once())->method('generate')->with($notification)->willReturn('token');
        $this->notificationRepository->expects(self::never())->method('save');

        ($this->controller)($notification, 'token');
    }

    public function testInvokeInvalidToken(): void
    {
        $notification = new RuleNotification();

        $this->tokenGenerator->expects(self::once())->method('generate')->with($notification)->willReturn('token');
        $this->notificationRepository->expects(self::never())->method('save');

        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('Invalid token');
        ($this->controller)($notification, 'foobar');
    }

    public function getController(): AbstractController
    {
        return new RuleNotificationReadController($this->tokenGenerator, $this->notificationRepository);
    }
}
