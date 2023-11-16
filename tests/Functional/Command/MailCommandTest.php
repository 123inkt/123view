<?php
declare(strict_types=1);

namespace DR\Review\Tests\Functional\Command;

use DR\Review\Git\GitRepository;
use DR\Review\Repository\Config\ExternalLinkRepository;
use DR\Review\Repository\Config\RuleNotificationRepository;
use DR\Review\Repository\Config\RuleRepository;
use DR\Review\Service\Git\CacheableGitRepositoryService;
use DR\Review\Service\Git\GitRepositoryService;
use DR\Review\Service\Revision\RevisionFetchService;
use DR\Review\Tests\AbstractKernelTestCase;
use DR\Review\Tests\Helper\MessageEventCollector;
use Exception;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Mailer\Event\MessageEvent;

#[CoversNothing]
class MailCommandTest extends AbstractKernelTestCase
{
    private GitRepositoryService&MockObject   $repositoryService;
    private RuleRepository&MockObject         $ruleRepository;
    private ExternalLinkRepository&MockObject $linkRepository;
    private MessageEventCollector             $messageCollector;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->ruleRepository    = $this->createMock(RuleRepository::class);
        $this->linkRepository    = $this->createMock(ExternalLinkRepository::class);
        $this->repositoryService = $this->createMock(CacheableGitRepositoryService::class);
        $this->messageCollector  = new MessageEventCollector();

        $notificationRepository = $this->createMock(RuleNotificationRepository::class);
        $notificationRepository->method('save')->willReturnCallback(static fn($notification) => $notification->setId(123));

        // register mock repository service
        self::getContainer()->set(RuleRepository::class, $this->ruleRepository);
        self::getContainer()->set(RuleNotificationRepository::class, $notificationRepository);
        self::getContainer()->set(ExternalLinkRepository::class, $this->linkRepository);
        self::getContainer()->set(CacheableGitRepositoryService::class, $this->repositoryService);
        self::getContainer()->set(RevisionFetchService::class, $this->createMock(RevisionFetchService::class));

        // register MessageEventCollector to subscribe to send e-mails
        /** @var EventDispatcherInterface|null $dispatcher */
        $dispatcher = self::getContainer()->get(EventDispatcherInterface::class);
        static::assertNotNull($dispatcher);
        $dispatcher->addListener(MessageEvent::class, [$this->messageCollector, 'onMessage']);
    }

    public function testMail(): void
    {
        // setup data
        $this->linkRepository->method('findAll')->willReturn($this->loadFixture('links.php'));
        $this->ruleRepository->method('getActiveRulesForFrequency')->willReturn($this->loadFixture('rules.php'));

        // setup repository mocks
        $repository = $this->createMock(GitRepository::class);
        $this->repositoryService->method('getRepository')->willReturn($repository);
        $repository->method('execute')->willReturn($this->getFileContents('git-log-commits.txt'));

        // start application and find the `mail`-command
        $command = (new Application(static::$kernel))->find('mail');

        // execute command
        $commandTester = new CommandTester($command);
        $exitCode      = $commandTester->execute(['--frequency' => 'once-per-hour']);
        static::assertSame(Command::SUCCESS, $exitCode);

        // assert 1 mail was send
        $messageEvents = $this->messageCollector->getMessageEvents();
        static::assertCount(2, $messageEvents);

        // assert it was TemplateEmail
        $message = $messageEvents[0]->getMessage();
        static::assertInstanceOf(TemplatedEmail::class, $message);

        // assert html contents
        $html = $message->getHtmlBody();
        static::assertIsString($html);
        static::assertStringContainsString('New revision by Sherlock Holmes in branch', $html);
        static::assertStringContainsString('<span class="fileName" style="color: #CC7832; font-weight: bold;">.gitlab-ci.yml</span>', $html);
    }
}
