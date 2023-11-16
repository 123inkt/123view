<?php
declare(strict_types=1);

namespace DR\Review\Service\Mail;

use DR\Review\Entity\Git\Commit;
use DR\Review\Entity\Notification\RuleConfiguration;
use DR\Review\Entity\Notification\RuleNotification;
use DR\Review\ViewModelProvider\Mail\CommitsViewModelProvider;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

class CommitMailService implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private readonly string $applicationName,
        private readonly MailerInterface $mailer,
        private readonly MailSubjectFormatter $subjectFormatter,
        private readonly CommitsViewModelProvider $viewModelProvider
    ) {
    }

    /**
     * @param Commit[] $commits
     *
     * @throws TransportExceptionInterface
     */
    public function sendCommitsMail(RuleConfiguration $config, array $commits, RuleNotification $notification): void
    {
        $rule    = $config->rule;
        $subject = $rule->getRuleOptions()?->getSubject() ?? sprintf('[%s] New revisions for: {name}', $this->applicationName);

        // create ViewModel and TemplateMail
        $email = (new TemplatedEmail())
            ->subject($this->subjectFormatter->format($subject, $rule, $commits))
            ->htmlTemplate('mail/mail.commits.html.twig')
            ->text('')
            ->context(['viewModel' => $this->viewModelProvider->getCommitsViewModel($commits, $rule, $notification)]);

        foreach ($rule->getRecipients() as $recipient) {
            $email->addTo(new Address($recipient->getEmail(), $recipient->getName() ?? ''));
            $this->logger?->info(sprintf('Sending %s commit mail to %s.', $rule->getName(), $recipient->getEmail()));
        }
        $this->mailer->send($email);
    }
}
