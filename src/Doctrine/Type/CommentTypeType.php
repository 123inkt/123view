<?php
declare(strict_types=1);

namespace DR\Review\Doctrine\Type;

use DR\Review\Entity\Review\CommentTypeEnum;

class CommentTypeType extends AbstractEnumType
{
    public const string TYPE   = 'enum_comment_type';
    public const array  VALUES = [CommentTypeEnum::Draft->value, CommentTypeEnum::Final->value];
}
