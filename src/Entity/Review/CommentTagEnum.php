<?php
declare(strict_types=1);

namespace DR\Review\Entity\Review;

enum CommentTagEnum: string
{
    case Suggestion = 'suggestion';
    case NiceToHave = 'nice_to_have';
    case ChangeRequest = 'change_request';
    case Explanation = 'explanation';
}
