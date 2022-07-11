<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Service\Mail;

use DateTime;
use DR\GitCommitNotification\Entity\Config\Recipient;
use DR\GitCommitNotification\Entity\Config\Rule;
use DR\GitCommitNotification\Entity\Config\RuleConfiguration;
use DR\GitCommitNotification\Entity\Config\RuleOptions;
use DR\GitCommitNotification\Service\Mail\MailService;
use DR\GitCommitNotification\Service\Mail\MailSubjectFormatter;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Service\Mail\MailService
 * @covers ::__construct
 */
class MailServiceTest extends AbstractTestCase
{
    /** @var MockObject&MailerInterface */
    private MailerInterface $mailer;
    /** @var MockObject&MailSubjectFormatter */
    private MailSubjectFormatter $formatter;
    private MailService          $service;
    private Rule                 $rule;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mailer    = $this->createMock(MailerInterface::class);
        $this->formatter = $this->createMock(MailSubjectFormatter::class);
        $this->service   = new MailService($this->log, $this->mailer, $this->formatter);

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
        $config  = new RuleConfiguration(new DateTime(), new DateTime(), [], $this->rule);

        // assert mailer send argument
        $this->formatter->expects(self::once())->method('format')->with('subject', $this->rule, $commits)->willReturn('replaced-subject');
        $this->mailer->expects(static::once())
            ->method('send')
            ->with(
                static::callback(
                    static fn(TemplatedEmail $email) => count($email->getTo()) > 0
                        && $email->getHtmlTemplate() === 'mail/commits.html.twig'
                        && $email->getSubject() === 'replaced-subject'
                        && $email->getTextBody() === ''
                        && count($email->getContext()) > 0
                )
            );

        $this->service->sendCommitsMail($config, $commits);
    }
}
