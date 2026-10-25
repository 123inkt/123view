<?php
declare(strict_types=1);

namespace DR\Review\Entity\Review;

enum CommentTypeEnum: string
{
    case Draft = 'draft';
    case Final = 'final';
}
