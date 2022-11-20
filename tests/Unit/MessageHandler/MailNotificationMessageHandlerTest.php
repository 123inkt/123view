<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\MessageHandler;

use DR\GitCommitNotification\Message\Comment\CommentAdded;
use DR\GitCommitNotification\Message\Delay\DelayableMessage;
use DR\GitCommitNotification\Message\MailNotificationInterface;
use DR\GitCommitNotification\MessageHandler\Mail\MailNotificationHandlerInterface;
use DR\GitCommitNotification\MessageHandler\Mail\MailNotificationHandlerProvider;
use DR\GitCommitNotification\MessageHandler\MailNotificationMessageHandler;
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
    private MailNotificationHandlerProvider&MockObject $handlerProvider;
    private MessageBusInterface&MockObject             $bus;
    private Envelope                                   $envelope;
    private MailNotificationMessageHandler             $handler;

    public function setUp(): void
    {
        parent::setUp();
        $this->envelope        = new Envelope(new stdClass(), []);
        $this->handlerProvider = $this->createMock(MailNotificationHandlerProvider::class);
        $this->bus             = $this->createMock(MessageBusInterface::class);
        $this->handler         = new MailNotificationMessageHandler($this->handlerProvider, $this->bus, 1000);
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
     * @throws Throwable
     */
    public function testHandleDelayedMessageUnknownHandlerShouldSkip(): void
    {
        $this->handlerProvider->expects(self::once())->method('getHandler')->with(stdClass::class)->willReturn(null);

        $this->handler->handleDelayedMessage(new DelayableMessage(new stdClass()));
    }

    /**
     * @covers ::handleDelayedMessage
     * @throws Throwable
     */
    public function testHandleDelayedMessage(): void
    {
        $commentAdded        = new CommentAdded(1, 2);
        $notificationHandler = $this->createMock(MailNotificationHandlerInterface::class);

        $this->handlerProvider->expects(self::once())->method('getHandler')->with(CommentAdded::class)->willReturn($notificationHandler);
        $notificationHandler->expects(self::once())->method('handle')->with($commentAdded);

        $this->handler->handleDelayedMessage(new DelayableMessage($commentAdded));
    }
}
