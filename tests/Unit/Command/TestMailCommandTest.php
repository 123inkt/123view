<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Command;

use DR\Review\Command\TestMailCommand;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Mailer\MailerInterface;

#[CoversClass(TestMailCommand::class)]
class TestMailCommandTest extends AbstractTestCase
{
    private MailerInterface&MockObject $mailer;
    private TestMailCommand            $command;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mailer  = $this->createMock(MailerInterface::class);
        $this->command = new TestMailCommand($this->mailer);
    }

    public function testConfigure(): void
    {
        $this->mailer->expects($this->never())->method('send');
        static::assertSame('test:mail', $this->command->getName());

        // test options
        $arguments = $this->command->getDefinition()->getArguments();
        static::assertCount(1, $arguments);
        static::assertArrayHasKey('address', $arguments);
        static::assertSame('address', $arguments['address']->getName());
    }

    public function testCommandInvalidConfig(): void
    {
        $commandTester = new CommandTester($this->command);
        $this->mailer->expects($this->once())->method('send');

        static::assertSame(Command::SUCCESS, $commandTester->execute(['address' => 'sherlock@example.com']));
    }
}
