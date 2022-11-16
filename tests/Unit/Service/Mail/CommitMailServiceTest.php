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
use DR\GitCommitNotification\Service\Mail\CommitMailService;
use DR\GitCommitNotification\Service\Mail\MailSubjectFormatter;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Service\Mail\CommitMailService
 * @covers ::__construct
 */
class CommitMailServiceTest extends AbstractTestCase
{
    private MailerInterface&MockObject      $mailer;
    private MailSubjectFormatter&MockObject $formatter;
    private CommitMailService               $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mailer    = $this->createMock(MailerInterface::class);
        $this->formatter = $this->createMock(MailSubjectFormatter::class);
        $this->service   = new CommitMailService($this->mailer, $this->formatter);
    }

    /**
     * @covers ::sendCommitsMail
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

        // prep data
        $commits = [$this->createCommit(), $this->createCommit()];
        $config  = new RuleConfiguration(new DatePeriod(new DateTime(), new DateInterval('PT1H'), new DateTime()), $rule);

        // assert mailer send argument
        $this->formatter->expects(self::once())->method('format')->with('subject', $rule, $commits)->willReturn('replaced-subject');
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

        $this->service->sendCommitsMail($config, $commits);
    }
}
