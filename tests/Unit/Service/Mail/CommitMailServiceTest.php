<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Mail;

use DateInterval;
use DatePeriod;
use DateTime;
use DR\Review\Doctrine\Type\MailThemeType;
use DR\Review\Entity\Notification\Recipient;
use DR\Review\Entity\Notification\Rule;
use DR\Review\Entity\Notification\RuleConfiguration;
use DR\Review\Entity\Notification\RuleNotification;
use DR\Review\Entity\Notification\RuleOptions;
use DR\Review\Service\Mail\CommitMailService;
use DR\Review\Service\Mail\MailSubjectFormatter;
use DR\Review\Tests\AbstractTestCase;
use DR\Review\ViewModel\Mail\CommitsViewModel;
use DR\Review\ViewModelProvider\Mail\CommitsViewModelProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;

#[CoversClass(CommitMailService::class)]
class CommitMailServiceTest extends AbstractTestCase
{
    private MailerInterface&MockObject          $mailer;
    private MailSubjectFormatter&MockObject     $formatter;
    private CommitsViewModelProvider&MockObject $viewModelProvider;
    private CommitMailService                   $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mailer            = $this->createMock(MailerInterface::class);
        $this->formatter         = $this->createMock(MailSubjectFormatter::class);
        $this->viewModelProvider = $this->createMock(CommitsViewModelProvider::class);
        $this->service           = new CommitMailService('foobar', $this->mailer, $this->formatter, $this->viewModelProvider);
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function testSendCommitsMail(): void
    {
        $recipient = new Recipient();
        $recipient->setEmail('recipient@example.com');

        $rule = new Rule();
        $rule->setName("Sherlock Holmes");
        $rule->addRecipient($recipient);
        $rule->setRuleOptions((new RuleOptions())->setSubject('subject'));

        $notification = new RuleNotification();

        // prep data
        $commits = [$this->createCommit(), $this->createCommit()];
        $config  = new RuleConfiguration(new DatePeriod(new DateTime(), new DateInterval('PT1H'), new DateTime()), $rule);

        $viewModel = new CommitsViewModel([], MailThemeType::UPSOURCE);

        // assert mailer send argument
        $this->formatter->expects($this->once())->method('format')->with('subject', $rule, $commits)->willReturn('replaced-subject');
        $this->viewModelProvider->expects($this->once())->method('getCommitsViewModel')->with($commits, $rule, $notification)->willReturn($viewModel);
        $this->mailer->expects(static::once())
            ->method('send')
            ->with(
                static::callback(
                    static fn(TemplatedEmail $email) => count($email->getTo()) > 0
                        && $email->getHtmlTemplate() === 'mail/mail.commits.html.twig'
                        && $email->getSubject() === 'replaced-subject'
                        && $email->getTextBody() === ''
                        && count($email->getContext()) > 0
                )
            );

        $this->service->sendCommitsMail($config, $commits, $notification);
    }
}
