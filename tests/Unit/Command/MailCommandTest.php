<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Command;

use DR\Review\Command\MailCommand;
use DR\Review\Entity\Notification\Rule;
use DR\Review\Entity\Notification\RuleConfiguration;
use DR\Review\Repository\Config\RuleRepository;
use DR\Review\Service\Mail\CommitMailService;
use DR\Review\Service\RuleProcessor;
use DR\Review\Tests\AbstractTestCase;
use Exception;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @coversDefaultClass \DR\Review\Command\MailCommand
 * @covers ::__construct
 */
class MailCommandTest extends AbstractTestCase
{
    private RuleProcessor&MockObject     $ruleProcessor;
    private RuleRepository&MockObject    $ruleRepository;
    private CommitMailService&MockObject $mailService;
    private MailCommand                  $command;

    protected function setUp(): void
    {
        parent::setUp();
        $this->ruleProcessor  = $this->createMock(RuleProcessor::class);
        $this->ruleRepository = $this->createMock(RuleRepository::class);
        $this->mailService    = $this->createMock(CommitMailService::class);
        $this->command        = new MailCommand($this->ruleRepository, $this->ruleProcessor, $this->mailService);
    }

    /**
     * @covers ::configure
     */
    public function testConfigure(): void
    {
        static::assertSame('mail', $this->command->getName());

        // test options
        $options = $this->command->getDefinition()->getOptions();
        static::assertCount(1, $options);
        static::assertArrayHasKey('frequency', $options);
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
    public function testCommandSuccessfulWithoutCommitsShouldNotMail(): void
    {
        $rule = new Rule();
        $rule->setName('foobar');

        // setup mocks
        $this->ruleRepository->expects(self::once())->method('getActiveRulesForFrequency')->with(true, 'once-per-hour')->willReturn([$rule]);

        $this->ruleProcessor
            ->expects(static::once())
            ->method('processRule')
            ->with(static::callback(static fn(RuleConfiguration $config) => $config->rule === $rule))
            ->willReturn([]);

        $this->mailService->expects(self::never())->method('sendCommitsMail');

        $commandTester = new CommandTester($this->command);
        $exitCode      = $commandTester->execute(['--frequency' => 'once-per-hour']);
        static::assertSame(Command::SUCCESS, $exitCode);
    }

    /**
     * @covers ::execute
     */
    public function testCommandSuccessfulWithCommits(): void
    {
        $rule = new Rule();
        $rule->setName('foobar');
        $commits = [$this->createCommit()];

        // setup mocks
        $this->ruleRepository->expects(self::once())->method('getActiveRulesForFrequency')->with(true, 'once-per-hour')->willReturn([$rule]);

        $this->ruleProcessor
            ->expects(static::once())
            ->method('processRule')
            ->with(static::callback(static fn(RuleConfiguration $config) => $config->rule === $rule))
            ->willReturn($commits);

        $this->mailService
            ->expects(self::once())
            ->method('sendCommitsMail')
            ->with(static::callback(static fn(RuleConfiguration $config) => $config->rule === $rule), $commits);

        $commandTester = new CommandTester($this->command);
        $exitCode      = $commandTester->execute(['--frequency' => 'once-per-hour']);
        static::assertSame(Command::SUCCESS, $exitCode);
    }

    /**
     * @covers ::execute
     */
    public function testCommandShouldExitWithFailureOnException(): void
    {
        $rule = new Rule();
        $rule->setName('foobar');

        // setup mocks
        $this->ruleRepository->expects(self::once())->method('getActiveRulesForFrequency')->with(true, 'once-per-hour')->willReturn([$rule]);
        $this->ruleProcessor->expects(static::once())->method('processRule')->willThrowException(new Exception('error'));

        $commandTester = new CommandTester($this->command);
        $exitCode      = $commandTester->execute(['--frequency' => 'once-per-hour']);
        static::assertSame(Command::FAILURE, $exitCode);
    }
}
