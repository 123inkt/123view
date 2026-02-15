<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Notification;

use DateInterval;
use DatePeriod;
use DateTime;
use DR\PHPUnitExtensions\Symfony\ClockTestTrait;
use DR\Review\Entity\Notification\Rule;
use DR\Review\Entity\Notification\RuleNotification;
use DR\Review\Repository\Config\RuleNotificationRepository;
use DR\Review\Service\Notification\RuleNotificationService;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bridge\PhpUnit\ClockMock;

#[CoversClass(RuleNotificationService::class)]
class RuleNotificationServiceTest extends AbstractTestCase
{
    use ClockTestTrait;

    private RuleNotificationRepository&MockObject $repository;
    private RuleNotificationService               $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = $this->createMock(RuleNotificationRepository::class);
        $this->service    = new RuleNotificationService($this->repository);
        ClockMock::register(static::class);
        ClockMock::register(RuleNotificationService::class);
        ClockMock::withClockMock(123456789);
    }

    public function testAddRuleNotification(): void
    {
        $start = (new DateTime())->setTimestamp(123456);
        $end   = (new DateTime())->setTimestamp(654321);

        $rule   = new Rule();
        $period = new DatePeriod($start, new DateInterval('PT1H'), $end);

        $expected = (new RuleNotification())
            ->setRule($rule)
            ->setNotifyTimestamp(654321)
            ->setCreateTimestamp(self::time());

        $this->repository->expects($this->once())->method('save')->with($expected, true);

        $this->service->addRuleNotification($rule, $period);
    }
}
