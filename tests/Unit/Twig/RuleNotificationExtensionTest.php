<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Twig;

use DR\Review\Entity\User\User;
use DR\Review\Repository\Config\RuleNotificationRepository;
use DR\Review\Tests\AbstractTestCase;
use DR\Review\Twig\RuleNotificationExtension;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Throwable;

#[CoversClass(RuleNotificationExtension::class)]
class RuleNotificationExtensionTest extends AbstractTestCase
{
    private RuleNotificationRepository&MockObject $notificationRepository;
    private RuleNotificationExtension             $extension;

    public function setUp(): void
    {
        parent::setUp();
        $this->notificationRepository = $this->createMock(RuleNotificationRepository::class);
        $this->extension              = new RuleNotificationExtension(new User(), $this->notificationRepository);
    }

    /**
     * @throws Throwable
     */
    public function testGetNotificationCount(): void
    {
        $this->notificationRepository->expects($this->once())->method('getUnreadNotificationCountForUser')->willReturn(5);
        static::assertSame(5, $this->extension->getNotificationCount());
        static::assertSame(5, $this->extension->getNotificationCount());
    }

    /**
     * @throws Throwable
     */
    public function testGetNotificationCountWithoutUser(): void
    {
        $extension = new RuleNotificationExtension(null, $this->notificationRepository);

        $this->notificationRepository->expects(self::never())->method('getUnreadNotificationCountForUser');
        static::assertSame(0, $extension->getNotificationCount());
    }

    public function testGetFunctions(): void
    {
        $functions = $this->extension->getFunctions();

        static::assertCount(1, $functions);

        $function = $functions[0];
        static::assertSame('rule_notification_count', $function->getName());
    }
}
