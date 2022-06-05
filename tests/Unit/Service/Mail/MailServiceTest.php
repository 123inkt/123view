<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Service\Mail;

use DR\GitCommitNotification\Entity\Config\Recipient;
use DR\GitCommitNotification\Entity\Config\Recipients;
use DR\GitCommitNotification\Entity\Config\Rule;
use DR\GitCommitNotification\Service\Mail\MailService;
use DR\GitCommitNotification\Tests\AbstractTest;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Service\Mail\MailService
 * @covers ::__construct
 */
class MailServiceTest extends AbstractTest
{
    /** @var MockObject|MailerInterface */
    private MailerInterface $mailer;
    private MailService     $service;
    private Rule            $rule;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mailer  = $this->createMock(MailerInterface::class);
        $this->service = new MailService($this->log, $this->mailer);

        $recipient        = new Recipient();
        $recipient->email = 'recipient@example.com';

        $this->rule             = new Rule();
        $this->rule->name       = "Sherlock Holmes";
        $this->rule->recipients = new Recipients();
        $this->rule->recipients->addRecipient($recipient);
    }

    /**
     * @covers ::sendCommitsMail
     * @throws TransportExceptionInterface
     */
    public function testSendCommitsMail(): void
    {
        // prep data
        $commits = [$this->createCommit(), $this->createCommit()];

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

        $this->service->sendCommitsMail($this->rule, $commits);
    }
}
