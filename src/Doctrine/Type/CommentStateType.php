<?php
declare(strict_types=1);

namespace DR\Review\Doctrine\Type;

use DR\Review\Entity\Review\CommentStateEnum;

class CommentStateType extends AbstractEnumType
{
    public const string TYPE   = 'enum_comment_state_type';
    public const array  VALUES = [CommentStateEnum::Open->value, CommentStateEnum::Resolved->value];
}
