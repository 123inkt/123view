<?php
declare(strict_types=1);

namespace DR\Review\Doctrine\Type;

class CommentStateType extends AbstractEnumType
{
    public const OPEN     = 'open';
    public const RESOLVED = 'resolved';

    public const string TYPE   = 'enum_comment_state_type';
    public const array  VALUES = [self::OPEN, self::RESOLVED];
}
