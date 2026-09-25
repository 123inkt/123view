<?php
declare(strict_types=1);

namespace DR\Review\Doctrine\Type;

class NotificationSendType extends AbstractEnumType
{
    public const MAIL    = 'mail';
    public const BROWSER = 'browser';
    public const BOTH    = 'both';

    public const string TYPE   = 'enum_notification_send';
    public const array  VALUES = [self::MAIL, self::BROWSER, self::BOTH];
}
