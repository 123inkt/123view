<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Command\Webhook;

use DR\Review\Command\Webhook\WebhookCleanUpCommand;
use DR\Review\Repository\Webhook\WebhookActivityRepository;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Tester\CommandTester;

#[CoversClass(WebhookCleanUpCommand::class)]
class WebhookCleanUpCommandTest extends AbstractTestCase
{
    private WebhookActivityRepository&MockObject $activityRepository;
    private WebhookCleanUpCommand                $command;

    protected function setUp(): void
    {
        parent::setUp();
        $this->activityRepository = $this->createMock(WebhookActivityRepository::class);
        $this->command            = new WebhookCleanUpCommand($this->activityRepository);
    }

    public function testExecute(): void
    {
        $this->activityRepository->expects($this->once())->method('cleanUp')->willReturn(5);

        $tester = new CommandTester($this->command);
        $tester->execute([]);

        static::assertSame(0, $tester->getStatusCode());
        static::assertSame("Removed 5 webhook activities", trim($tester->getDisplay()));
    }
}
