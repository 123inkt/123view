<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Command;

use DR\Review\Command\MailCommand;
use DR\Review\Entity\Notification\Rule;
use DR\Review\Entity\Notification\RuleConfiguration;
use DR\Review\Entity\Notification\RuleOptions;
use DR\Review\Entity\User\User;
use DR\Review\Repository\Config\RuleRepository;
use DR\Review\Security\Role\Roles;
use DR\Review\Service\Mail\CommitMailService;
use DR\Review\Service\Notification\RuleNotificationService;
use DR\Review\Service\Revision\RevisionFetchService;
use DR\Review\Service\RuleProcessor;
use DR\Review\Tests\AbstractTestCase;
use Exception;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

#[CoversClass(MailCommand::class)]
class MailCommandTest extends AbstractTestCase
{
    private RuleProcessor&MockObject           $ruleProcessor;
    private RuleRepository&MockObject          $ruleRepository;
    private RevisionFetchService&MockObject    $revisionFetchService;
    private RuleNotificationService&MockObject $notificationService;
    private CommitMailService&MockObject       $mailService;
    private MailCommand                        $command;
    private User                               $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = new User();
        $this->user->setRoles([Roles::ROLE_USER]);

        $this->ruleProcessor        = $this->createMock(RuleProcessor::class);
        $this->ruleRepository       = $this->createMock(RuleRepository::class);
        $this->revisionFetchService = $this->createMock(RevisionFetchService::class);
        $this->notificationService  = $this->createMock(RuleNotificationService::class);
        $this->mailService          = $this->createMock(CommitMailService::class);
        $this->command              = new MailCommand(
            $this->ruleRepository,
            $this->ruleProcessor,
            $this->revisionFetchService,
            $this->notificationService,
            $this->mailService
        );
    }

    public function testConfigure(): void
    {
        static::assertSame('mail', $this->command->getName());

        // test options
        $options = $this->command->getDefinition()->getOptions();
        static::assertCount(1, $options);
        static::assertArrayHasKey('frequency', $options);
        static::assertSame('frequency', $options['frequency']->getName());
    }

    public function testCommandInvalidFrequency(): void
    {
        $commandTester = new CommandTester($this->command);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid or missing `frequency` argument');
        $commandTester->execute(['--frequency' => 'foobar']);
    }

    public function testCommandSuccessfulWithoutCommitsShouldNotMail(): void
    {
        $rule = new Rule();
        $rule->setName('foobar');
        $rule->setUser($this->user);

        // setup mocks
        $this->ruleRepository->expects($this->once())->method('getActiveRulesForFrequency')->with(true, 'once-per-hour')->willReturn([$rule]);
        $this->revisionFetchService->expects($this->once())->method('fetchRevisionsForRules')->with([$rule]);
        $this->notificationService->expects(self::never())->method('addRuleNotification');
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

    public function testCommandSuccessfulWithCommits(): void
    {
        $rule = new Rule();
        $rule->setName('foobar');
        $rule->setUser($this->user);
        $rule->setRuleOptions(new RuleOptions());
        $commits = [$this->createCommit()];

        // setup mocks
        $this->ruleRepository->expects($this->once())->method('getActiveRulesForFrequency')->with(true, 'once-per-hour')->willReturn([$rule]);
        $this->revisionFetchService->expects($this->once())->method('fetchRevisionsForRules')->with([$rule]);
        $this->notificationService->expects($this->once())->method('addRuleNotification')->with($rule);
        $this->ruleProcessor
            ->expects(static::once())
            ->method('processRule')
            ->with(static::callback(static fn(RuleConfiguration $config) => $config->rule === $rule))
            ->willReturn($commits);

        $this->mailService
            ->expects($this->once())
            ->method('sendCommitsMail')
            ->with(static::callback(static fn(RuleConfiguration $config) => $config->rule === $rule), $commits);

        $commandTester = new CommandTester($this->command);
        $exitCode      = $commandTester->execute(['--frequency' => 'once-per-hour']);
        static::assertSame(Command::SUCCESS, $exitCode);
    }

    public function testCommandShouldSkipUserWithoutUserRole(): void
    {
        $this->user->setRoles([]);
        $rule = new Rule();
        $rule->setName('foobar');
        $rule->setUser($this->user);

        // setup mocks
        $this->ruleRepository->expects($this->once())->method('getActiveRulesForFrequency')->with(true, 'once-per-hour')->willReturn([$rule]);
        $this->revisionFetchService->expects($this->once())->method('fetchRevisionsForRules')->with([$rule]);
        $this->notificationService->expects(self::never())->method('addRuleNotification');
        $this->ruleProcessor->expects(static::never())->method('processRule');
        $this->mailService->expects(self::never())->method('sendCommitsMail');

        $commandTester = new CommandTester($this->command);
        $exitCode      = $commandTester->execute(['--frequency' => 'once-per-hour']);
        static::assertSame(Command::SUCCESS, $exitCode);
    }

    public function testCommandShouldExitWithFailureOnException(): void
    {
        $rule = new Rule();
        $rule->setName('foobar');
        $rule->setUser($this->user);

        // setup mocks
        $this->ruleRepository->expects($this->once())->method('getActiveRulesForFrequency')->with(true, 'once-per-hour')->willReturn([$rule]);
        $this->revisionFetchService->expects($this->once())->method('fetchRevisionsForRules')->with([$rule]);
        $this->ruleProcessor->expects(static::once())->method('processRule')->willThrowException(new Exception('error'));

        $commandTester = new CommandTester($this->command);
        $exitCode      = $commandTester->execute(['--frequency' => 'once-per-hour']);
        static::assertSame(Command::FAILURE, $exitCode);
    }
}
