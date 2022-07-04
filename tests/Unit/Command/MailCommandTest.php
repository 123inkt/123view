<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Command;

use DR\GitCommitNotification\Command\MailCommand;
use DR\GitCommitNotification\Entity\Config\ExternalLink;
use DR\GitCommitNotification\Entity\Config\Rule;
use DR\GitCommitNotification\Entity\Config\RuleConfiguration;
use DR\GitCommitNotification\Repository\Config\ExternalLinkRepository;
use DR\GitCommitNotification\Repository\Config\RuleRepository;
use DR\GitCommitNotification\Service\RuleProcessor;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use Exception;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Command\MailCommand
 * @covers ::__construct
 */
class MailCommandTest extends AbstractTestCase
{
    /** @var RuleProcessor&MockObject */
    private RuleProcessor $ruleProcessor;
    /** @var ExternalLinkRepository&MockObject */
    private ExternalLinkRepository $externalLinkRepository;
    /** @var RuleRepository&MockObject */
    private RuleRepository $ruleRepository;
    private MailCommand    $command;

    protected function setUp(): void
    {
        parent::setUp();
        $this->ruleProcessor = $this->createMock(RuleProcessor::class);

        $this->externalLinkRepository = $this->createMock(ExternalLinkRepository::class);
        $this->ruleRepository         = $this->createMock(RuleRepository::class);

        $this->command = new MailCommand($this->ruleRepository, $this->externalLinkRepository, $this->ruleProcessor);
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
    public function testCommandSuccessful(): void
    {
        $rule = new Rule();
        $rule->setName('foobar');
        $externalLink = new ExternalLink();

        // setup mocks
        $this->externalLinkRepository->expects(self::once())->method('findAll')->willReturn([$externalLink]);
        $this->ruleRepository->expects(self::once())->method('getActiveRulesForFrequency')->with(true, 'once-per-hour')->willReturn([$rule]);

        $this->ruleProcessor
            ->expects(static::once())
            ->method('processRule')
            ->with(static::callback(static fn(RuleConfiguration $config) => $config->rule === $rule && count($config->externalLinks) === 1));

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
        $externalLink = new ExternalLink();

        // setup mocks
        $this->externalLinkRepository->expects(self::once())->method('findAll')->willReturn([$externalLink]);
        $this->ruleRepository->expects(self::once())->method('getActiveRulesForFrequency')->with(true, 'once-per-hour')->willReturn([$rule]);
        $this->ruleProcessor->expects(static::once())->method('processRule')->willThrowException(new Exception('error'));

        $commandTester = new CommandTester($this->command);
        $exitCode      = $commandTester->execute(['--frequency' => 'once-per-hour']);
        static::assertSame(Command::FAILURE, $exitCode);
    }
}
