<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\E2E;

use DR\GitCommitNotification\Git\GitRepository;
use DR\GitCommitNotification\Service\Git\CacheableGitRepositoryService;
use DR\GitCommitNotification\Service\Git\GitRepositoryService;
use DR\GitCommitNotification\Tests\AbstractKernelTestCase;
use DR\GitCommitNotification\Tests\Helper\MessageEventCollector;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Mailer\Event\MessageEvent;

/**
 * @coversNothing
 */
class MailCommandTestCase extends AbstractKernelTestCase
{
    /** @var GitRepositoryService&MockObject */
    private GitRepositoryService  $repositoryService;
    private MessageEventCollector $messageCollector;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repositoryService = $this->createMock(CacheableGitRepositoryService::class);
        $this->messageCollector  = new MessageEventCollector();
        // register mock repository service
        self::getContainer()->set(CacheableGitRepositoryService::class, $this->repositoryService);
        // register MessageEventCollector to subscribe to send e-mails
        /** @var EventDispatcherInterface|null $dispatcher */
        $dispatcher = self::getContainer()->get(EventDispatcherInterface::class);
        static::assertNotNull($dispatcher);
        $dispatcher->addListener(MessageEvent::class, [$this->messageCollector, 'onMessage']);
    }

    public function testMail(): void
    {
        // setup repository mocks
        $repository = $this->createMock(GitRepository::class);
        $this->repositoryService->method('getRepository')->with('https://example.com/detectives/sherlock.git')->willReturn($repository);
        $repository->method('execute')->willReturn($this->getFileContents('git-log-commits.txt'));

        // start application and find the `mail`-command
        $command = (new Application(static::$kernel))->find('mail');

        // execute command
        $commandTester = new CommandTester($command);
        $exitCode      = $commandTester->execute(['--frequency' => 'once-per-hour', '--config' => $this->getFilePath('config.xml')->getPathname()]);
        static::assertSame(Command::SUCCESS, $exitCode);

        // assert 1 mail was send
        $messageEvents = $this->messageCollector->getMessageEvents();
        static::assertCount(1, $messageEvents);

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
