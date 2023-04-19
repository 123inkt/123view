<?php
declare(strict_types=1);

namespace DR\Review\Service\Mail;

use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\CommentReply;
use DR\Review\Entity\User\User;
use DR\Review\Utility\Arrays;
use DR\Review\Utility\Assert;
use DR\Review\ViewModelProvider\Mail\MailCommentViewModelProvider;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Contracts\Translation\TranslatorInterface;
use Throwable;

class CommentMailService implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private TranslatorInterface $translator,
        private MailerInterface $mailer,
        private MailRecipientService $recipientService,
        private MailCommentViewModelProvider $viewModelProvider
    ) {
    }

    /**
     * @param User[]|null $recipients
     *
     * @throws Throwable
     */
    public function sendNewCommentMail(CodeReview $review, Comment $comment, ?array $recipients = null): void
    {
        if ($recipients === null) {
            $recipients = $this->recipientService->getUsersForReview($review);
            $recipients = array_merge($recipients, $this->recipientService->getUserForComment($comment));
        }
        $recipients = Arrays::remove(Arrays::unique($recipients), Assert::notNull($comment->getUser()));

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

        /** @var User $recipient */
        foreach ($recipients as $recipient) {
            if ($recipient->getSetting()->isMailCommentAdded() === false) {
                continue;
            }

            $email->addBcc(new Address((string)$recipient->getEmail(), (string)$recipient->getName()));
            $this->logger?->info(sprintf('Sending mail to %s for comment %d.', $recipient->getEmail(), $comment->getId()));
        }

        if (count($email->getBcc()) === 0) {
            return;
        }

        $this->mailer->send($email);
    }

    /**
     * @param User[]|null $recipients
     *
     * @throws Throwable
     */
    public function sendNewCommentReplyMail(CodeReview $review, Comment $comment, CommentReply $reply, ?array $recipients = null): void
    {
        if ($recipients === null) {
            $recipients = $this->recipientService->getUsersForReview($review);
            $recipients = array_merge($recipients, $this->recipientService->getUserForComment($comment));
            $recipients = array_merge($recipients, $this->recipientService->getUsersForReply($comment, $reply));
        }
        $recipients = Arrays::remove(Arrays::unique($recipients), Assert::notNull($reply->getUser()));

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

        /** @var User $recipient */
        foreach ($recipients as $recipient) {
            if ($recipient->getSetting()->isMailCommentReplied() === false) {
                continue;
            }

            $email->addBcc(new Address((string)$recipient->getEmail(), (string)$recipient->getName()));
            $this->logger?->info(sprintf('Sending mail to %s for comment %d.', $recipient->getEmail(), $reply->getId()));
        }

        if (count($email->getBcc()) === 0) {
            return;
        }

        $this->mailer->send($email);
    }

    /**
     * @throws Throwable
     */
    public function sendCommentResolvedMail(CodeReview $review, Comment $comment, User $resolvedBy): void
    {
        $recipients = $this->recipientService->getUsersForReview($review);
        $recipients = array_merge($recipients, $this->recipientService->getUserForComment($comment));
        $recipients = array_merge($recipients, $this->recipientService->getUsersForReply($comment));
        $recipients = Arrays::remove(Arrays::unique($recipients), $resolvedBy);

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

        /** @var User $recipient */
        foreach ($recipients as $recipient) {
            if ($recipient->getSetting()->isMailCommentResolved() === false || $recipient->getEmail() === $resolvedBy->getEmail()) {
                continue;
            }

            $email->addBcc(new Address((string)$recipient->getEmail(), (string)$recipient->getName()));
            $this->logger?->info(
                sprintf(
                    'Sending mail to %s for resolved comment %d resolved by %s',
                    $recipient->getEmail(),
                    $comment->getId(),
                    $resolvedBy->getEmail()
                )
            );
        }

        if (count($email->getBcc()) === 0) {
            return;
        }

        $this->mailer->send($email);
    }
}
