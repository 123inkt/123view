<?php
declare(strict_types=1);

namespace DR\Review\Doctrine\Type;

use DR\Review\Entity\Review\CommentTagEnum;

class CommentTagType extends AbstractEnumType
{
    public const string TYPE   = 'enum_comment_tag_type';
    protected const array VALUES = [
        CommentTagEnum::ChangeRequest->value,
        CommentTagEnum::Explanation->value,
        CommentTagEnum::NiceToHave->value,
        CommentTagEnum::Suggestion->value
    ];
}
