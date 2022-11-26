<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\MessageHandler\Mail;

use DR\GitCommitNotification\Message\MailNotificationInterface;

interface MailNotificationHandlerInterface
{
    public function handle(MailNotificationInterface $message): void;

    public static function accepts(): string;
}
