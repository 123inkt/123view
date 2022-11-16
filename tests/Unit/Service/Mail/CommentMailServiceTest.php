<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Service\Mail;

use DR\GitCommitNotification\Service\Mail\MailRecipientService;
use DR\GitCommitNotification\Service\Mail\CommitMailService;
use DR\GitCommitNotification\Service\Mail\MailSubjectFormatter;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use DR\GitCommitNotification\ViewModelProvider\Mail\MailCommentViewModelProvider;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Service\Mail\CommentMailService
 * @covers ::__construct
 */
class CommentMailServiceTest extends AbstractTestCase
{
    private MailerInterface&MockObject              $mailer;
    private MailSubjectFormatter&MockObject         $formatter;
    private TranslatorInterface&MockObject          $translator;
    private MailRecipientService&MockObject         $recipientService;
    private MailCommentViewModelProvider&MockObject $viewModelProvider;
    private CommitMailService                       $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mailer            = $this->createMock(MailerInterface::class);
        $this->formatter         = $this->createMock(MailSubjectFormatter::class);
        $this->translator        = $this->createMock(TranslatorInterface::class);
        $this->recipientService  = $this->createMock(MailRecipientService::class);
        $this->viewModelProvider = $this->createMock(MailCommentViewModelProvider::class);
        $this->service           = new CommitMailService(
            $this->translator,
            $this->mailer,
            $this->formatter,
            $this->recipientService,
            $this->viewModelProvider
        );
    }

    /**
     * @covers ::sendNewCommentMail
     */
    public function testSendNewCommentMail(): void
    {
    }

    /**
     * @covers ::sendCommentResolvedMail
     */
    public function testSendCommentResolvedMail(): void
    {
    }

    /**
     * @covers ::sendNewCommentReplyMail
     */
    public function testSendNewCommentReplyMail(): void
    {
    }
}
