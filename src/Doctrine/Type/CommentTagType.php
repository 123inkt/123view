<?php
declare(strict_types=1);

namespace DR\Review\Doctrine\Type;

use DR\Review\Entity\Review\CommentTagEnum;

class CommentTagType extends AbstractEnumType
{
    public const    TYPE   = 'enum_comment_tag_type';
    public const    VALUES = [
        CommentTagEnum::ChangeRequest->value,
        CommentTagEnum::Explanation->value,
        CommentTagEnum::NiceToHave->value,
        CommentTagEnum::Suggestion->value
    ];
}
