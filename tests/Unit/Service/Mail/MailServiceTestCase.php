<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Service\Mail;

use DateTime;
use DR\GitCommitNotification\Entity\Recipient;
use DR\GitCommitNotification\Entity\Rule;
use DR\GitCommitNotification\Entity\RuleConfiguration;
use DR\GitCommitNotification\Entity\RuleOptions;
use DR\GitCommitNotification\Service\Mail\MailService;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Service\Mail\MailService
 * @covers ::__construct
 */
class MailServiceTestCase extends AbstractTestCase
{
    /** @var MockObject&MailerInterface */
    private MailerInterface $mailer;
    private MailService     $service;
    private Rule            $rule;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mailer  = $this->createMock(MailerInterface::class);
        $this->service = new MailService($this->log, $this->mailer);

        $recipient = new Recipient();
        $recipient->setEmail('recipient@example.com');

        $this->rule = new Rule();
        $this->rule->setName("Sherlock Holmes");
        $this->rule->addRecipient($recipient);
        $this->rule->setRuleOptions(new RuleOptions());
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
        $this->mailer->expects(static::once())
            ->method('send')
            ->with(
                static::callback(
                    static fn(TemplatedEmail $email) => count($email->getTo()) > 0
                        && $email->getHtmlTemplate() === 'mail/commits.html.twig'
                        && $email->getSubject() === '[Commit Notification] New revisions for: Sherlock Holmes'
                        && $email->getTextBody() === ''
                        && count($email->getContext()) > 0
                )
            );

        $this->service->sendCommitsMail($config, $commits);
    }
}
