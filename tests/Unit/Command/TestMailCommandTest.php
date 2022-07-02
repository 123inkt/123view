<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Command;

use DR\GitCommitNotification\Command\TestMailCommand;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Mailer\MailerInterface;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Command\TestMailCommand
 * @covers ::__construct
 */
class TestMailCommandTest extends AbstractTestCase
{
    /** @var MailerInterface&MockObject */
    private MailerInterface $mailer;
    private TestMailCommand $command;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mailer  = $this->createMock(MailerInterface::class);
        $this->command = new TestMailCommand($this->mailer);
    }

    /**
     * @covers ::configure
     */
    public function testConfigure(): void
    {
        static::assertSame('test:mail', $this->command->getName());

        // test options
        $arguments = $this->command->getDefinition()->getArguments();
        static::assertCount(1, $arguments);
        static::assertArrayHasKey('address', $arguments);
        static::assertSame('address', $arguments['address']->getName());
    }

    /**
     * @covers ::execute
     */
    public function testCommandInvalidConfig(): void
    {
        $commandTester = new CommandTester($this->command);
        $this->mailer->expects(static::once())->method('send');

        static::assertSame(Command::SUCCESS, $commandTester->execute(['address' => 'sherlock@example.com']));
    }
}
