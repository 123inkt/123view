<?php
declare(strict_types=1);

namespace DR\Review\Entity\Notification;

enum RuleNotificationReadEnum: string
{
    case READ = 'read';
    case UNREAD = 'unread';
}
