<?php
declare(strict_types=1);

namespace DR\Review\Doctrine\Type;

class FilterType extends AbstractEnumType
{
    public const FILE    = 'file';
    public const AUTHOR  = 'author';
    public const SUBJECT = 'subject';

    public const string TYPE   = 'enum_filter_type';
    public const array VALUES = [self::FILE, self::AUTHOR, self::SUBJECT];
}
