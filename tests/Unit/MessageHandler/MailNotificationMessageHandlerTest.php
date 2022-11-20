<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\MessageHandler;

use DR\GitCommitNotification\Message\Comment\CommentAdded;
use DR\GitCommitNotification\Message\Delay\DelayableMessage;
use DR\GitCommitNotification\Message\MailNotificationInterface;
use DR\GitCommitNotification\MessageHandler\MailNotificationMessageHandler;
use DR\GitCommitNotification\Repository\Review\CommentReplyRepository;
use DR\GitCommitNotification\Repository\Review\CommentRepository;
use DR\GitCommitNotification\Repository\User\UserRepository;
use DR\GitCommitNotification\Service\CodeReview\Comment\CommentMentionService;
use DR\GitCommitNotification\Service\Mail\CommentMailService;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use stdClass;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;
use Throwable;

/**
 * @coversDefaultClass \DR\GitCommitNotification\MessageHandler\MailNotificationMessageHandler
 * @covers ::__construct
 */
class MailNotificationMessageHandlerTest extends AbstractTestCase
{
    private CommentMailService&MockObject     $mailService;
    private CommentRepository&MockObject      $commentRepository;
    private CommentReplyRepository&MockObject $replyRepository;
    private UserRepository&MockObject         $userRepository;
    private CommentMentionService&MockObject  $mentionService;
    private MessageBusInterface&MockObject    $bus;
    private Envelope                          $envelope;
    private MailNotificationMessageHandler    $handler;

    public function setUp(): void
    {
        parent::setUp();
        $this->envelope          = new Envelope(new stdClass(), []);
        $this->mailService       = $this->createMock(CommentMailService::class);
        $this->commentRepository = $this->createMock(CommentRepository::class);
        $this->replyRepository   = $this->createMock(CommentReplyRepository::class);
        $this->userRepository    = $this->createMock(UserRepository::class);
        $this->mentionService    = $this->createMock(CommentMentionService::class);
        $this->bus               = $this->createMock(MessageBusInterface::class);
        $this->handler           = new MailNotificationMessageHandler(
            $this->mailService,
            $this->commentRepository,
            $this->replyRepository,
            $this->userRepository,
            $this->mentionService,
            $this->bus,
            1000
        );
    }

    /**
     * @covers ::delayMessage
     * @throws Throwable
     */
    public function testDelayMessage(): void
    {
        $message = new CommentAdded(1, 2);

        $this->bus->expects(self::once())->method('dispatch')
            ->with(
                self::callback(
                    static function ($envelope) use ($message) {
                        static::assertInstanceOf(Envelope::class, $envelope);
                        $envelopeMessage = $envelope->getMessage();
                        static::assertInstanceOf(DelayableMessage::class, $envelopeMessage);
                        static::assertSame($message, $envelopeMessage->message);

                        $stamp = $envelope->last(DelayStamp::class);
                        static::assertInstanceOf(DelayStamp::class, $stamp);
                        static::assertSame(1000, $stamp->getDelay());

                        return true;
                    }
                )
            )
            ->willReturn($this->envelope);
        $this->handler->delayMessage($message);
    }

    /**
     * @covers ::getHandledMessages
     */
    public function testGetHandledMessages(): void
    {
        $entries = [...MailNotificationMessageHandler::getHandledMessages()];

        static::assertSame(
            [
                MailNotificationInterface::class => ['method' => 'delayMessage', 'from_transport' => 'async_messages'],
                DelayableMessage::class          => ['method' => 'handleDelayedMessage', 'from_transport' => 'async_delay_mail']
            ],
            $entries
        );
    }

    /**
     * @covers ::handleDelayedMessage
     */
    public function testHandleCommentAdded(): void
    {
    }
}
