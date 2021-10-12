<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service\Mail;

use DR\GitCommitNotification\Entity\Config\Rule;
use DR\GitCommitNotification\Entity\Git\Commit;
use DR\GitCommitNotification\ViewModel\CommitsViewModel;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

class MailService
{
    private MailerInterface $mailer;
    private LoggerInterface $log;

    public function __construct(LoggerInterface $log, MailerInterface $mailer)
    {
        $this->mailer = $mailer;
        $this->log    = $log;
    }

    /**
     * @param Commit[] $commits
     *
     * @throws TransportExceptionInterface
     */
    public function sendCommitsMail(Rule $rule, array $commits): void
    {
        $externalLinks = $rule->externalLinks === null ? [] : $rule->externalLinks->getExternalLinks();

        // create ViewModel and TemplateMail
        $email = (new TemplatedEmail())
            ->subject($rule->subject ?? '[GitCommitMail] New revisions for: ' . $rule->name)
            ->htmlTemplate('mail/commits.html.twig')
            ->text('')
            ->context(['viewModel' => new CommitsViewModel($commits, $rule->theme, $externalLinks)]);

        foreach ($rule->recipients->getRecipients() as $recipient) {
            $email->addTo(new Address($recipient->email, $recipient->name ?? ''));
            $this->log->info(sprintf('Sending %s commit mail to %s.', $rule->name, $recipient->email));
        }
        $this->mailer->send($email);
    }
}
