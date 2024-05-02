<?php
declare(strict_types=1);

namespace DR\Review\MessageHandler;

use DR\Review\Message\Delay\DelayableMessage;
use DR\Review\Message\MailNotificationInterface;
use DR\Review\MessageHandler\Mail\MailNotificationHandlerProvider;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;
use Symfony\Component\Messenger\Stamp\HandlerArgumentsStamp;
use Throwable;

class MailNotificationMessageHandler implements LoggerAwareInterface
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
    #[AsMessageHandler(fromTransport: 'async_messages')]
    public function delayMessage(MailNotificationInterface $message): void
    {
        $logMessage = 'MailNotificationMessageHandler: delay message for {delay} seconds: {class}';
        $this->logger?->info($logMessage, ['delay' => $this->mailNotificationDelay / 1000, 'class' => get_class($message)]);

        $this->bus->dispatch(new Envelope(new DelayableMessage($message), [new DelayStamp($this->mailNotificationDelay), new HandlerArgumentsStamp(
            $additionalArguments)]));
    }

    /**
     * Stage 2: a delayed mail notification message was received, dispatch to appropriate handler.
     * @throws Throwable
     */
    #[AsMessageHandler(fromTransport: 'async_delay_mail')]
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
}
