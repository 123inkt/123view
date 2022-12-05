<?php
declare(strict_types=1);

namespace DR\Review\MessageHandler\Mail;

use DR\Review\Message\MailNotificationInterface;

interface MailNotificationHandlerInterface
{
    public function handle(MailNotificationInterface $message): void;

    public static function accepts(): string;
}
