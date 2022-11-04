<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Service\Mail;

use DateInterval;
use DatePeriod;
use DateTime;
use DR\GitCommitNotification\Entity\Config\Recipient;
use DR\GitCommitNotification\Entity\Config\Rule;
use DR\GitCommitNotification\Entity\Config\RuleConfiguration;
use DR\GitCommitNotification\Entity\Config\RuleOptions;
use DR\GitCommitNotification\Service\Mail\MailRecipientService;
use DR\GitCommitNotification\Service\Mail\MailService;
use DR\GitCommitNotification\Service\Mail\MailSubjectFormatter;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use DR\GitCommitNotification\ViewModelProvider\Mail\MailCommentViewModelProvider;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Service\Mail\MailService
 * @covers ::__construct
 */
class MailServiceTest extends AbstractTestCase
{
    private MailerInterface&MockObject              $mailer;
    private MailSubjectFormatter&MockObject         $formatter;
    private TranslatorInterface&MockObject          $translator;
    private MailRecipientService&MockObject         $recipientService;
    private MailCommentViewModelProvider&MockObject $viewModelProvider;
    private MailService                             $service;
    private Rule                                    $rule;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mailer            = $this->createMock(MailerInterface::class);
        $this->formatter         = $this->createMock(MailSubjectFormatter::class);
        $this->translator        = $this->createMock(TranslatorInterface::class);
        $this->recipientService  = $this->createMock(MailRecipientService::class);
        $this->viewModelProvider = $this->createMock(MailCommentViewModelProvider::class);
        $this->service           = new MailService(
            $this->translator,
            $this->mailer,
            $this->formatter,
            $this->recipientService,
            $this->viewModelProvider
        );

        $recipient = new Recipient();
        $recipient->setEmail('recipient@example.com');

        $this->rule = new Rule();
        $this->rule->setName("Sherlock Holmes");
        $this->rule->addRecipient($recipient);
        $this->rule->setRuleOptions((new RuleOptions())->setSubject('subject'));
    }

    /**
     * @covers ::sendCommitsMail
     * @throws TransportExceptionInterface
     */
    public function testSendCommitsMail(): void
    {
        // prep data
        $commits = [$this->createCommit(), $this->createCommit()];
        $config  = new RuleConfiguration(new DatePeriod(new DateTime(), new DateInterval('PT1H'), new DateTime()), $this->rule);

        // assert mailer send argument
        $this->formatter->expects(self::once())->method('format')->with('subject', $this->rule, $commits)->willReturn('replaced-subject');
        $this->mailer->expects(static::once())
            ->method('send')
            ->with(
                static::callback(
                    static fn(TemplatedEmail $email) => count($email->getTo()) > 0
                        && $email->getHtmlTemplate() === 'mail/commit/commits.html.twig'
                        && $email->getSubject() === 'replaced-subject'
                        && $email->getTextBody() === ''
                        && count($email->getContext()) > 0
                )
            );

        $this->service->sendCommitsMail($config, $commits);
    }
}
