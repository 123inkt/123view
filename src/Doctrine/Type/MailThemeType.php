<?php
declare(strict_types=1);

namespace DR\Review\Doctrine\Type;

class MailThemeType extends AbstractEnumType
{
    public const UPSOURCE = 'upsource';
    public const DARCULA  = 'darcula';

    public const string TYPE   = 'enum_mail_theme';
    protected const array VALUES = [self::UPSOURCE, self::DARCULA];
}
