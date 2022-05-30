<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Doctrine\Type;

class MailThemeType extends AbstractEnumType
{
    public const UPSOURCE = 'upsource';
    public const DARCULA  = 'darcula';

    public const    TYPE   = 'enum_mail_theme';
    protected const VALUES = [self::UPSOURCE, self::DARCULA];
}
