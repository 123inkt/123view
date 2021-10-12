<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Command;

use DR\GitCommitNotification\Command\MailCommand;
use DR\GitCommitNotification\Entity\Config\Configuration;
use DR\GitCommitNotification\Entity\Config\Rule;
use DR\GitCommitNotification\Exception\ConfigException;
use DR\GitCommitNotification\Service\Config\ConfigLoader;
use DR\GitCommitNotification\Service\RuleProcessor;
use DR\GitCommitNotification\Tests\AbstractTest;
use Exception;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Command\MailCommand
 * @covers ::__construct
 */
class MailCommandTest extends AbstractTest
{
    /** @var ConfigLoader|MockObject */
    private ConfigLoader $configLoader;
    /** @var RuleProcessor|MockObject */
    private RuleProcessor $ruleProcessor;
    private MailCommand $command;

    protected function setUp(): void
    {
        parent::setUp();
        $this->configLoader  = $this->createMock(ConfigLoader::class);
        $this->ruleProcessor = $this->createMock(RuleProcessor::class);

        $this->command = new MailCommand($this->log, $this->configLoader, $this->ruleProcessor);
    }

    /**
     * @covers ::configure
     */
    public function testConfigure(): void
    {
        static::assertSame('mail', $this->command->getName());

        // test options
        $options = $this->command->getDefinition()->getOptions();
        static::assertCount(2, $options);
        static::assertArrayHasKey('config', $options);
        static::assertArrayHasKey('frequency', $options);
        static::assertSame('config', $options['config']->getName());
        static::assertSame('frequency', $options['frequency']->getName());
    }

    /**
     * @covers ::execute
     */
    public function testCommandInvalidFrequency(): void
    {
        $commandTester = new CommandTester($this->command);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid or missing `frequency` argument');
        $commandTester->execute(['--frequency' => 'foobar']);
    }

    /**
     * @covers ::execute
     */
    public function testCommandInvalidConfig(): void
    {
        $commandTester = new CommandTester($this->command);
        $this->configLoader->expects(static::once())->method('load')->willThrowException(new ConfigException());

        static::assertSame(Command::FAILURE, $commandTester->execute(['--frequency' => 'once-per-hour']));
    }

    /**
     * @covers ::execute
     */
    public function testCommandInactiveRuleShouldBeSkipped(): void
    {
        $rule         = new Rule();
        $rule->active = false;
        $rule->name   = 'foobar';

        $config = new Configuration();
        $config->addRule($rule);

        $this->configLoader->method('load')->willReturn($config);
        $this->ruleProcessor->expects(static::never())->method('processRule');

        $commandTester = new CommandTester($this->command);
        $exitCode      = $commandTester->execute(['--frequency' => 'once-per-hour']);
        static::assertSame(Command::SUCCESS, $exitCode);
    }

    /**
     * @covers ::execute
     */
    public function testCommandRuleFrequencyShouldMatchCliFrequency(): void
    {
        $rule            = new Rule();
        $rule->frequency = 'once-per-two-hours';
        $rule->name      = 'foobar';

        $config = new Configuration();
        $config->addRule($rule);

        $this->configLoader->method('load')->willReturn($config);
        $this->ruleProcessor->expects(static::never())->method('processRule');

        $commandTester = new CommandTester($this->command);
        $exitCode      = $commandTester->execute(['--frequency' => 'once-per-hour']);
        static::assertSame(Command::SUCCESS, $exitCode);
    }

    /**
     * @covers ::execute
     */
    public function testCommandProcessRuleShouldBeInvoked(): void
    {
        $rule       = new Rule();
        $rule->name = 'foobar';

        $config = new Configuration();
        $config->addRule($rule);

        $this->configLoader->method('load')->willReturn($config);
        $this->ruleProcessor->expects(static::once())->method('processRule')->with($rule);

        $commandTester = new CommandTester($this->command);
        $exitCode      = $commandTester->execute(['--frequency' => 'once-per-hour']);
        static::assertSame(Command::SUCCESS, $exitCode);
    }

    /**
     * @covers ::execute
     */
    public function testCommandShouldExitWithFailureOnException(): void
    {
        $rule       = new Rule();
        $rule->name = 'foobar';
        $exception  = new Exception('error');

        $config = new Configuration();
        $config->addRule($rule);

        $this->configLoader->method('load')->willReturn($config);
        $this->ruleProcessor->method('processRule')->willThrowException($exception);

        $commandTester = new CommandTester($this->command);
        $exitCode      = $commandTester->execute(['--frequency' => 'once-per-hour']);
        static::assertSame(Command::FAILURE, $exitCode);
    }
}
