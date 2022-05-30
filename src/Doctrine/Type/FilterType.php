<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Doctrine\Type;

class FilterType extends AbstractEnumType
{
    public const FILE    = 'file';
    public const AUTHOR  = 'author';
    public const SUBJECT = 'subject';

    public const    TYPE   = 'enum_filter_type';
    protected const VALUES = [self::FILE, self::AUTHOR, self::SUBJECT];
}
