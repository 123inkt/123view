<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service\Mail;

use DR\GitCommitNotification\Entity\Config\RuleConfiguration;
use DR\GitCommitNotification\Entity\Git\Commit;
use DR\GitCommitNotification\Entity\Review\CodeReview;
use DR\GitCommitNotification\Entity\Review\Comment;
use DR\GitCommitNotification\ViewModel\Mail\CommitsViewModel;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Contracts\Translation\TranslatorInterface;

class MailService implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private TranslatorInterface $translator,
        private MailerInterface $mailer,
        private MailSubjectFormatter $subjectFormatter
    ) {
    }

    /**
     * @param Address[] $recipients
     *
     * @throws TransportExceptionInterface
     */
    public function sendCommentMail(CodeReview $review, Comment $comment, array $recipients): void
    {
        $subject = $this->translator->trans(
            'mail.new.comment.subject',
            ['reviewId' => 'CR-' . $review->getProjectId(), 'reviewTitle' => $review->getTitle()]
        );

        // create ViewModel and TemplateMail
        $email = (new TemplatedEmail())
            ->subject($subject)
            ->htmlTemplate('mail/comment/comment.html.twig')
            ->text('')
            ->context(['comment' => $comment]);

        foreach ($recipients as $recipient) {
            $email->addBcc($recipient);
            $this->logger?->info(sprintf('Sending mail to %s for comment %d.', $recipient->getAddress(), $comment->getId()));
        }
        $this->mailer->send($email);
    }

    /**
     * @param Commit[] $commits
     *
     * @throws TransportExceptionInterface
     */
    public function sendCommitsMail(RuleConfiguration $config, array $commits): void
    {
        $rule = $config->rule;
        $subject = $rule->getRuleOptions()?->getSubject() ?? '[Commit Notification] New revisions for: {name}';

        // create ViewModel and TemplateMail
        $email = (new TemplatedEmail())
            ->subject($this->subjectFormatter->format($subject, $rule, $commits))
            ->htmlTemplate('mail/commit/commits.html.twig')
            ->text('')
            ->context(['viewModel' => new CommitsViewModel($commits, $rule->getRuleOptions()?->getTheme() ?? 'upsource', $config->externalLinks)]);

        foreach ($rule->getRecipients() as $recipient) {
            $email->addTo(new Address((string)$recipient->getEmail(), $recipient->getName() ?? ''));
            $this->logger?->info(sprintf('Sending %s commit mail to %s.', $rule->getName(), $recipient->getEmail()));
        }
        $this->mailer->send($email);
    }
}
