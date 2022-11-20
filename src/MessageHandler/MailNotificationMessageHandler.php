<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\MessageHandler;

use DR\GitCommitNotification\Message\Delay\DelayableMessage;
use DR\GitCommitNotification\Message\MailNotificationInterface;
use DR\GitCommitNotification\MessageHandler\Mail\MailNotificationHandlerProvider;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;
use Throwable;

#[AsMessageHandler]
class MailNotificationMessageHandler implements MessageSubscriberInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private readonly MailNotificationHandlerProvider $handlerProvider,
        private readonly MessageBusInterface $bus,
        private readonly int $mailNotificationDelay
    ) {
    }

    /**
     * Stage 1: any mail notification message should be resubmitted with a xxx seconds delay
     * @throws Throwable
     */
    public function delayMessage(MailNotificationInterface $message): void
    {
        $logMessage = 'MailNotificationMessageHandler: delay message for {delay} seconds: {class}';
        $this->logger?->info($logMessage, ['delay' => $this->mailNotificationDelay / 1000, 'class' => get_class($message)]);

        $this->bus->dispatch(new Envelope(new DelayableMessage($message), [new DelayStamp($this->mailNotificationDelay)]));
    }

    /**
     * Stage 2: a delayed mail notification message was received, dispatch to appropriate handler.
     * @throws Throwable
     */
    public function handleDelayedMessage(DelayableMessage $message): void
    {
        $this->logger?->info('MailNotificationMessageHandler: delayed message received: ' . get_class($message->message));

        $notificationMessage = $message->message;
        $handler             = $this->handlerProvider->getHandler(get_class($notificationMessage));
        if ($handler === null) {
            $this->logger?->error('Failed to retrieve MailNotificationHandler for {class}', ['class' => get_class($notificationMessage)]);

            return;
        }

        assert($notificationMessage instanceof MailNotificationInterface);
        $handler->handle($notificationMessage);
    }

    /**
     * @return iterable<string, array<string, string>>
     */
    public static function getHandledMessages(): iterable
    {
        yield MailNotificationInterface::class => ['method' => 'delayMessage', 'from_transport' => 'async_messages'];
        yield DelayableMessage::class => ['method' => 'handleDelayedMessage', 'from_transport' => 'async_delay_mail'];
    }
}
