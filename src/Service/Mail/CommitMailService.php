<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service\Mail;

use DR\GitCommitNotification\Entity\Git\Commit;
use DR\GitCommitNotification\Entity\Notification\RuleConfiguration;
use DR\GitCommitNotification\ViewModel\Mail\CommitsViewModel;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

class CommitMailService implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(private MailerInterface $mailer, private MailSubjectFormatter $subjectFormatter)
    {
    }

    /**
     * @param Commit[] $commits
     *
     * @throws TransportExceptionInterface
     */
    public function sendCommitsMail(RuleConfiguration $config, array $commits): void
    {
        $rule    = $config->rule;
        $subject = $rule->getRuleOptions()?->getSubject() ?? '[Commit Notification] New revisions for: {name}';

        // create ViewModel and TemplateMail
        $email = (new TemplatedEmail())
            ->subject($this->subjectFormatter->format($subject, $rule, $commits))
            ->htmlTemplate('mail/mail.commits.html.twig')
            ->text('')
            ->context(['viewModel' => new CommitsViewModel($commits, $rule->getRuleOptions()?->getTheme() ?? 'upsource')]);

        foreach ($rule->getRecipients() as $recipient) {
            $email->addTo(new Address((string)$recipient->getEmail(), $recipient->getName() ?? ''));
            $this->logger?->info(sprintf('Sending %s commit mail to %s.', $rule->getName(), $recipient->getEmail()));
        }
        $this->mailer->send($email);
    }
}
