<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Mail;

use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\CommentReply;
use DR\Review\Entity\User\User;
use DR\Review\Service\Mail\CommentMailService;
use DR\Review\Service\Mail\MailRecipientService;
use DR\Review\Tests\AbstractTestCase;
use DR\Review\ViewModelProvider\Mail\MailCommentViewModelProvider;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Throwable;

/**
 * @coversDefaultClass \DR\Review\Service\Mail\CommentMailService
 * @covers ::__construct
 */
class CommentMailServiceTest extends AbstractTestCase
{
    private MailerInterface&MockObject              $mailer;
    private TranslatorInterface&MockObject          $translator;
    private MailRecipientService&MockObject         $recipientService;
    private MailCommentViewModelProvider&MockObject $viewModelProvider;
    private CommentMailService                      $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mailer            = $this->createMock(MailerInterface::class);
        $this->translator        = $this->createMock(TranslatorInterface::class);
        $this->recipientService  = $this->createMock(MailRecipientService::class);
        $this->viewModelProvider = $this->createMock(MailCommentViewModelProvider::class);
        $this->service           = new CommentMailService(
            $this->translator,
            $this->mailer,
            $this->recipientService,
            $this->viewModelProvider
        );
    }

    /**
     * @covers ::sendNewCommentMail
     * @throws Throwable
     */
    public function testSendNewCommentMailShouldNotMailWithoutRecipients(): void
    {
        $user    = new User();
        $comment = new Comment();
        $comment->setUser($user);
        $review = new CodeReview();
        $review->setProjectId(123);

        $this->recipientService->expects(self::once())->method('getUsersForReview')->with($review)->willReturn([$user]);
        $this->recipientService->expects(self::once())->method('getUserForComment')->with($comment)->willReturn([$user]);
        $this->translator->expects(self::once())->method('trans');
        $this->mailer->expects(self::never())->method('send');

        $this->service->sendNewCommentMail($review, $comment);
    }

    /**
     * @covers ::sendNewCommentMail
     * @throws Throwable
     */
    public function testSendNewCommentMailShouldSendMail(): void
    {
        $userA = new User();
        $userA->setId(5);
        $userA->setEmail('sherlock@example.com');
        $userA->getSetting()->setMailCommentAdded(true);
        $userB = new User();
        $userB->setId(6);
        $userB->setEmail('watson@example.com');
        $userB->getSetting()->setMailCommentAdded(true);
        $userC = new User();
        $userC->setId(7);
        $userC->setEmail('enola@example.com');
        $userC->getSetting()->setMailCommentAdded(false);
        $comment = new Comment();
        $comment->setUser($userA);
        $review = new CodeReview();
        $review->setProjectId(123);

        $this->recipientService->expects(self::once())->method('getUsersForReview')->with($review)->willReturn([$userA, $userB, $userC]);
        $this->recipientService->expects(self::once())->method('getUserForComment')->with($comment)->willReturn([$userA]);
        $this->translator->expects(self::once())->method('trans')->with('mail.new.comment.subject')->willReturn('subject');
        $this->viewModelProvider->expects(self::once())->method('createCommentViewModel')->with($review, $comment);

        $this->mailer->expects(self::once())->method('send')
            ->with(
                self::callback(
                    static function (TemplatedEmail $email) {
                        $addresses = $email->getBcc();
                        static::assertCount(1, $addresses);
                        static::assertSame('watson@example.com', $addresses[0]->getAddress());

                        return true;
                    }
                )
            );

        $this->service->sendNewCommentMail($review, $comment);
    }

    /**
     * @covers ::sendNewCommentReplyMail
     * @throws Throwable
     */
    public function testSendNewCommentReplyMailNoMailForEmptyRecipients(): void
    {
        $user = new User();
        $user->setId(5);
        $comment = new Comment();
        $comment->setUser($user);
        $reply = new CommentReply();
        $reply->setUser($user);
        $review = new CodeReview();
        $review->setProjectId(123);
        $review->setTitle('title');

        $this->recipientService->expects(self::once())->method('getUsersForReview')->with($review)->willReturn([$user]);
        $this->recipientService->expects(self::once())->method('getUserForComment')->with($comment)->willReturn([$user]);
        $this->recipientService->expects(self::once())->method('getUsersForReply')->with($comment, $reply)->willReturn([$user]);
        $this->translator->expects(self::once())->method('trans');
        $this->mailer->expects(self::never())->method('send');

        $this->service->sendNewCommentReplyMail($review, $comment, $reply);
    }

    /**
     * @covers ::sendNewCommentReplyMail
     * @throws Throwable
     */
    public function testSendNewCommentReplyMail(): void
    {
        $userA = new User();
        $userA->setId(5);
        $userA->setEmail('sherlock@example.com');
        $userA->getSetting()->setMailCommentReplied(true);
        $userB = new User();
        $userB->setId(6);
        $userB->setEmail('watson@example.com');
        $userB->getSetting()->setMailCommentReplied(true);
        $userC = new User();
        $userC->setId(7);
        $userC->setEmail('enola@example.com');
        $userC->getSetting()->setMailCommentReplied(false);
        $comment = new Comment();
        $comment->setUser($userA);
        $reply = new CommentReply();
        $reply->setUser($userA);
        $review = new CodeReview();

        $this->recipientService->expects(self::once())->method('getUsersForReview')->with($review)->willReturn([$userA, $userB, $userC]);
        $this->recipientService->expects(self::once())->method('getUserForComment')->with($comment)->willReturn([$userA]);
        $this->recipientService->expects(self::once())->method('getUsersForReply')->with($comment, $reply)->willReturn([$userA]);
        $this->translator->expects(self::once())->method('trans')->with('mail.updated.comment.subject')->willReturn('subject');
        $this->viewModelProvider->expects(self::once())->method('createCommentViewModel')->with($review, $comment, $reply);

        $this->mailer->expects(self::once())->method('send')
            ->with(
                self::callback(
                    static function (TemplatedEmail $email) {
                        $addresses = $email->getBcc();
                        static::assertCount(1, $addresses);
                        static::assertSame('watson@example.com', $addresses[0]->getAddress());

                        return true;
                    }
                )
            );

        $this->service->sendNewCommentReplyMail($review, $comment, $reply);
    }

    /**
     * @covers ::sendCommentResolvedMail
     * @throws Throwable
     */
    public function testSendCommentResolvedMailNoRecipientsNoMail(): void
    {
        $user    = (new User())->setId(5);
        $comment = new Comment();
        $review  = new CodeReview();
        $review->setProjectId(123);
        $review->setTitle('title');

        $this->recipientService->expects(self::once())->method('getUsersForReview')->with($review)->willReturn([$user]);
        $this->recipientService->expects(self::once())->method('getUserForComment')->with($comment)->willReturn([$user]);
        $this->recipientService->expects(self::once())->method('getUsersForReply')->with($comment)->willReturn([$user]);
        $this->translator->expects(self::once())->method('trans');
        $this->mailer->expects(self::never())->method('send');

        $this->service->sendCommentResolvedMail($review, $comment, (new User())->setId(5));
    }

    /**
     * @covers ::sendCommentResolvedMail
     * @throws Throwable
     */
    public function testSendCommentResolvedMail(): void
    {
        $userA = new User();
        $userA->setId(5);
        $userA->setEmail('sherlock@example.com');
        $userA->getSetting()->setMailCommentResolved(true);
        $userB = new User();
        $userB->setId(6);
        $userB->setEmail('watson@example.com');
        $userB->getSetting()->setMailCommentResolved(true);
        $userC = new User();
        $userC->setId(7);
        $userC->setEmail('enola@example.com');
        $userC->getSetting()->setMailCommentResolved(false);
        $comment = new Comment();
        $comment->setUser($userA);
        $review = new CodeReview();

        $this->recipientService->expects(self::once())->method('getUsersForReview')->with($review)->willReturn([$userA, $userB, $userC]);
        $this->recipientService->expects(self::once())->method('getUserForComment')->with($comment)->willReturn([$userA]);
        $this->recipientService->expects(self::once())->method('getUsersForReply')->with($comment)->willReturn([$userA]);
        $this->translator->expects(self::once())->method('trans')->with('mail.comment.resolved.subject')->willReturn('subject');
        $this->viewModelProvider->expects(self::once())->method('createCommentViewModel')->with($review, $comment, null, $userA);

        $this->mailer->expects(self::once())->method('send')
            ->with(
                self::callback(
                    static function (TemplatedEmail $email) {
                        $addresses = $email->getBcc();
                        static::assertCount(1, $addresses);
                        static::assertSame('watson@example.com', $addresses[0]->getAddress());

                        return true;
                    }
                )
            );

        $this->service->sendCommentResolvedMail($review, $comment, $userA);
    }
}
