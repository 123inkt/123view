<?php
declare(strict_types=1);

namespace DR\Review\Entity\Review;

enum CommentModificationEnum: string
{
    case Local = 'local';
    case Gitlab = 'gitlab';
}
