<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\MessageHandler;

use DR\GitCommitNotification\Message\MailNotificationInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(fromTransport: 'async_delay_mail')]
class MailNotificationMessageHandler
{
    public function __invoke(MailNotificationInterface $event): void
    {
    }
}
