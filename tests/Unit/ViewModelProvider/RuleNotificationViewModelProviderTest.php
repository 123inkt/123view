<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\ViewModelProvider;

use Doctrine\DBAL\Exception;
use DR\Review\Entity\Notification\Rule;
use DR\Review\Entity\Notification\RuleNotification;
use DR\Review\Entity\User\User;
use DR\Review\Repository\Config\RuleNotificationRepository;
use DR\Review\Repository\Config\RuleRepository;
use DR\Review\Tests\AbstractTestCase;
use DR\Review\ViewModel\App\Notification\RuleNotificationViewModel;
use DR\Review\ViewModelProvider\RuleNotificationViewModelProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(RuleNotificationViewModelProvider::class)]
class RuleNotificationViewModelProviderTest extends AbstractTestCase
{
    private User&MockObject                       $user;
    private RuleRepository&MockObject             $ruleRepository;
    private RuleNotificationRepository&MockObject $notificationRepository;
    private RuleNotificationViewModelProvider     $viewModelProvider;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user                   = $this->createMock(User::class);
        $this->ruleRepository         = $this->createMock(RuleRepository::class);
        $this->notificationRepository = $this->createMock(RuleNotificationRepository::class);
        $this->viewModelProvider      = new RuleNotificationViewModelProvider($this->user, $this->ruleRepository, $this->notificationRepository);
    }

    /**
     * @throws Exception
     */
    public function testGetNotificationsViewModelDefault(): void
    {
        $this->notificationRepository->expects($this->once())->method('getUnreadNotificationPerRuleCount')->with($this->user)->willReturn([]);
        $this->ruleRepository->expects($this->once())
            ->method('findBy')
            ->with(['user' => $this->user, 'active' => true], ['name' => 'ASC'], 100)
            ->willReturn([]);
        $this->notificationRepository->expects(self::never())->method('findBy');

        $expected = new RuleNotificationViewModel(null, [], [], [], false);
        static::assertEquals($expected, $this->viewModelProvider->getNotificationsViewModel(null, false));
    }

    /**
     * @throws Exception
     */
    public function testGetNotificationsViewModel(): void
    {
        $rule         = (new Rule())->setId(123);
        $notification = new RuleNotification();

        $this->notificationRepository->expects($this->once())
            ->method('getUnreadNotificationPerRuleCount')
            ->with($this->user)
            ->willReturn([123 => 5]);
        $this->ruleRepository->expects($this->once())
            ->method('findBy')
            ->with(['user' => $this->user, 'active' => true], ['name' => 'ASC'], 100)
            ->willReturn([$rule]);
        $this->notificationRepository->expects($this->once())
            ->method('findBy')
            ->with(['rule' => $rule, 'read' => 0], ['createTimestamp' => 'DESC'], 100)
            ->willReturn([$notification]);

        $expected = new RuleNotificationViewModel($rule, [123 => 5], [123 => $rule], [$notification], true);

        static::assertEquals($expected, $this->viewModelProvider->getNotificationsViewModel(123, true));
    }
}
