<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service\Mail;

use DR\GitCommitNotification\Entity\Config\RuleConfiguration;
use DR\GitCommitNotification\Entity\Config\User;
use DR\GitCommitNotification\Entity\Git\Commit;
use DR\GitCommitNotification\Entity\Review\CodeReview;
use DR\GitCommitNotification\Entity\Review\Comment;
use DR\GitCommitNotification\Entity\Review\CommentReply;
use DR\GitCommitNotification\Utility\Arrays;
use DR\GitCommitNotification\Utility\Type;
use DR\GitCommitNotification\ViewModel\Mail\CommitsViewModel;
use DR\GitCommitNotification\ViewModelProvider\Mail\MailCommentViewModelProvider;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Contracts\Translation\TranslatorInterface;
use Throwable;

class MailService implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private TranslatorInterface $translator,
        private MailerInterface $mailer,
        private MailSubjectFormatter $subjectFormatter,
        private MailRecipientService $recipientService,
        private MailCommentViewModelProvider $viewModelProvider,
    ) {
    }

    /**
     * @throws Throwable
     */
    public function sendNewCommentMail(CodeReview $review, Comment $comment): void
    {
        $recipients = $this->recipientService->getUsersForReview($review);
        $recipients = Arrays::remove(array_unique($recipients), Type::notNull($comment->getUser()));

        $subject = $this->translator->trans(
            'mail.new.comment.subject',
            ['reviewId' => 'CR-' . $review->getProjectId(), 'reviewTitle' => $review->getTitle()]
        );

        // create ViewModel and TemplateMail
        $email = (new TemplatedEmail())
            ->subject($subject)
            ->htmlTemplate('mail/mail.comment.html.twig')
            ->text('')
            ->context(['commentModel' => $this->viewModelProvider->createCommentViewModel($review, $comment)]);

        foreach ($recipients as $recipient) {
            $email->addBcc(new Address((string)$recipient->getEmail(), (string)$recipient->getName()));
            $this->logger?->info(sprintf('Sending mail to %s for comment %d.', $recipient->getEmail(), $comment->getId()));
        }
        $this->mailer->send($email);
    }

    /**
     * @throws Throwable
     */
    public function sendNewCommentReplyMail(CodeReview $review, Comment $comment, CommentReply $reply): void
    {
        $recipients = $this->recipientService->getUsersForReview($review);
        $recipients = array_merge($recipients, $this->recipientService->getUsersForReply($comment, $reply));
        $recipients = Arrays::remove(array_unique($recipients), Type::notNull($reply->getUser()));

        $subject = $this->translator->trans(
            'mail.updated.comment.subject',
            ['reviewId' => 'CR-' . $review->getProjectId(), 'reviewTitle' => $review->getTitle()]
        );

        // create ViewModel and TemplateMail
        $email = (new TemplatedEmail())
            ->subject($subject)
            ->htmlTemplate('mail/mail.comment.html.twig')
            ->text('')
            ->context(['commentModel' => $this->viewModelProvider->createCommentViewModel($review, $comment, $reply)]);

        foreach ($recipients as $recipient) {
            $email->addBcc(new Address((string)$recipient->getEmail(), (string)$recipient->getName()));
            $this->logger?->info(sprintf('Sending mail to %s for comment %d.', $recipient->getEmail(), $reply->getId()));
        }
        $this->mailer->send($email);
    }

    /**
     * @throws Throwable
     */
    public function sendCommentResolvedMail(CodeReview $review, Comment $comment, User $resolvedBy): void
    {
        $recipients = $this->recipientService->getUsersForReview($review);
        $recipients = array_merge($recipients, $this->recipientService->getUsersForReply($comment));
        $recipients = Arrays::remove(array_unique($recipients), $resolvedBy);

        $subject = $this->translator->trans(
            'mail.comment.resolved.subject',
            ['reviewId' => 'CR-' . $review->getProjectId(), 'reviewTitle' => $review->getTitle()]
        );

        // create ViewModel and TemplateMail
        $email = (new TemplatedEmail())
            ->subject($subject)
            ->htmlTemplate('mail/mail.comment.html.twig')
            ->text('')
            ->context(['commentModel' => $this->viewModelProvider->createCommentViewModel($review, $comment, null, $resolvedBy)]);

        foreach ($recipients as $recipient) {
            $email->addBcc(new Address((string)$recipient->getEmail(), (string)$recipient->getName()));
            $this->logger?->info(sprintf('Sending mail to %s for resolved comment %d.', $recipient->getEmail(), $comment->getId()));
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
